<?php

namespace NUSWhispers\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use NUSWhispers\Events;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\User;
use Ramsey\Uuid\Uuid;

class ConfessionService
{
    /**
     * Returns confession count by fingerprint.
     *
     * @param \NUSWhispers\Models\Confession
     * @param string|null $status
     *
     * @return mixed
     */
    public function countByFingerprint(Confession $confession, $status = null)
    {
        return $this->buildFingerprintQuery($confession, $status)->count();
    }

    /**
     * Creates a new confession.
     *
     * @param array $attributes
     *
     * @return \NUSWhispers\Models\Confession
     */
    public function create(array $attributes): Confession
    {
        $attributes['status'] = 'Pending';

        $attributes = $this->checkFingerprint($attributes);
        $attributes = $this->normalize($attributes);

        $confession = Confession::create($attributes);
        $confession = $this->sync($confession, $attributes);

        event(new Events\ConfessionWasCreated($confession));
        $this->dispatchStatusEvents($confession);

        return $confession;
    }

    /**
     * Deletes a confession by its ID.
     *
     * @param mixed $confession
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function delete($confession)
    {
        $confession = $confession instanceof Confession ?
            $confession :
            Confession::query()->findOrFail($confession);

        $result = $confession->delete();

        event(new Events\ConfessionWasDeleted($confession, $this->resolveUser($confession)));

        return $result;
    }

    /**
     * Returns confessions by fingerprint.
     *
     * @param \NUSWhispers\Models\Confession
     * @param string|null $status
     *
     * @return mixed
     */
    public function findByFingerprint(Confession $confession, $status = null)
    {
        return $this->buildFingerprintQuery($confession, $status)->get();
    }

    /**
     * Updates a confession status.
     *
     * @param mixed $confession
     * @param string $status
     * @param int|null $hours
     *
     * @return \NUSWhispers\Models\Confession
     */
    public function updateStatus($confession, $status = 'Approved', $hours = null): Confession
    {
        return $this->update($confession, ['status' => $status, 'schedule' => $hours]);
    }

    /**
     * Updates a confession based on its ID.
     *
     * @param mixed $confession
     * @param array $attributes
     *
     * @return \NUSWhispers\Models\Confession
     */
    public function update($confession, array $attributes = []): Confession
    {
        $attributes = $this->normalize($attributes);

        $confession = $this->resolveConfession($confession);

        $originalStatus = $confession->status;

        // Do not allow switching status back to "Pending".
        if ($originalStatus !== 'Pending' && Arr::get($attributes, 'status', '') === 'Pending') {
            throw new InvalidArgumentException('Switching a non-pending confession back to "pending" status is not allowed.');
        }

        $confession->update($attributes);
        $confession = $this->sync($confession, $attributes);

        event(new Events\ConfessionWasUpdated($confession, $this->resolveUser($confession)));
        $this->dispatchStatusEvents($confession, $originalStatus);

        return $confession;
    }

    /**
     * Build fingerprint query.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param null $status
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildFingerprintQuery($confession, $status = null): Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder $builder */
        $builder = Confession::where('fingerprint', $confession->fingerprint);

        if ($status !== null) {
            $builder = $builder->where('status', $status);
        }

        return $builder;
    }

    /**
     * Check fingerprint.
     *
     * @param  array $attributes
     *
     * @return array
     */
    protected function checkFingerprint(array $attributes = []): array
    {
        $attributes['fingerprint'] = ! empty($attributes['token']) ?
            $attributes['token'] :
            $this->generateFingerprint();

        unset($attributes['token']);

        return $attributes;
    }

    /**
     * Dispatches the respective events if the status is changed.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param string $originalStatus
     *
     * @return void
     */
    protected function dispatchStatusEvents(Confession $confession, $originalStatus = ''): void
    {
        $newStatus = $confession->status;

        // Status change event should not trigger when confession is created.
        if (! empty($originalStatus) && $originalStatus !== $newStatus) {
            event(new Events\ConfessionStatusWasChanged(
                $confession,
                $originalStatus,
                $this->resolveUser($confession)
            ));
        }

        // Call scheduled event even though the status is the same.
        if ($newStatus === 'Scheduled') {
            event(new Events\ConfessionWasScheduled($confession, auth()->user()));

            return;
        }

        if ($originalStatus === $newStatus || $newStatus === 'Pending') {
            return;
        }

        $eventClass = '\NUSWhispers\Events\ConfessionWas' . $newStatus;
        event(new $eventClass($confession, $this->resolveUser($confession)));
    }

    /**
     * Generates a fingerprint.
     *
     * @return string
     */
    protected function generateFingerprint(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Schedules a confession to change status in x hours.
     *
     * @param mixed $confession
     * @param string $status
     * @param \DateTime $updateAt
     *
     * @return \NUSWhispers\Models\Confession
     */
    protected function schedule($confession, $status, $updateAt): Confession
    {
        $confession = $this->resolveConfession($confession);

        $confession->queue()->delete();
        $confession->queue()->create([
            'status_after' => $status,
            'update_status_at' => $updateAt,
        ]);

        return $confession;
    }

    /**
     * Normalize input.
     *
     * @param array $attributes
     * @return array
     */
    protected function normalize(array $attributes): array
    {
        $attributes['status_updated_at'] = Carbon::now();

        if (! empty($attributes['schedule'])) {
            $attributes['status_after'] = $attributes['status'];
            $attributes['status'] = 'Scheduled';

            $attributes['schedule'] = is_string($attributes['schedule']) ?
                Carbon::createFromTimestamp($attributes['schedule']) :
                Carbon::now()->addHours($attributes['schedule']);
        }

        return $attributes;
    }

    /**
     * Syncs confession's relations.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param array $attributes
     *
     * @return \NUSWhispers\Models\Confession
     */
    protected function sync(Confession $confession, array $attributes = []): Confession
    {
        if (isset($attributes['categories']) && is_array($attributes['categories'])) {
            $confession->categories()->sync($attributes['categories']);
        }

        if (! empty($attributes['schedule'])) {
            $this->schedule(
                $confession,
                $attributes['status_after'],
                $attributes['schedule']
            );
        }

        return $confession;
    }

    /**
     * Resolves the confession.
     *
     * @param mixed $confession
     *
     * @return mixed
     */
    protected function resolveConfession($confession)
    {
        if (! $confession instanceof Confession) {
            return Confession::query()->findOrFail($confession);
        }

        return $confession;
    }

    /**
     * Resolves the user who modified the confession.
     *
     * @param  \NUSWhispers\Models\Confession $confession
     *
     * @return \NUSWhispers\Models\User|null
     */
    protected function resolveUser(Confession $confession): ?User
    {
        if (auth()->check()) {
            return auth()->user();
        }

        $lastLog = $confession
            ->logs()
            ->orderBy('created_on', 'desc')
            ->with(['user'])
            ->first();

        return $lastLog ? $lastLog->user : null;
    }
}

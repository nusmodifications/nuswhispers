<?php

namespace NUSWhispers\Services;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use InvalidArgumentException;
use NUSWhispers\Models\Confession;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Events\ConfessionWasDeleted;
use NUSWhispers\Events\ConfessionWasUpdated;
use NUSWhispers\Events\ConfessionWasScheduled;
use NUSWhispers\Events\ConfessionStatusWasChanged;

class ConfessionService
{
    /**
     * Creates a new confession.
     *
     * @param array $attributes
     *
     * @return \NUSWhispers\Models\Confession
     */
    public function create(array $attributes)
    {
        $attributes['status'] = 'Pending';

        $attributes = $this->checkFingerprint($attributes);
        $attributes = $this->normalize($attributes);

        $confession = Confession::create($attributes);
        $confession = $this->sync($confession, $attributes);

        event(new ConfessionWasCreated($confession));
        $this->dispatchStatusEvents($confession);

        return $confession;
    }

    /**
     * Deletes a confession by its ID.
     *
     * @param $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $confession = Confession::findOrFail($id);

        $result = $confession->delete();

        event(new ConfessionWasDeleted($confession, $this->resolveUser($confession)));

        return $result;
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
    public function updateStatus($confession, $status = 'Approved', $hours = null)
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
    public function update($confession, array $attributes = [])
    {
        $attributes = $this->normalize($attributes);

        $confession = $this->resolveConfession($confession);

        $originalStatus = $confession->status;

        // Do not allow switching status back to "Pending".
        if ($originalStatus !== 'Pending' && array_get($attributes, 'status', '') === 'Pending') {
            throw new InvalidArgumentException('Switching a non-pending confession back to "pending" status is not allowed.');
        }

        $confession->update($attributes);
        $confession = $this->sync($confession, $attributes);

        event(new ConfessionWasUpdated($confession, $this->resolveUser($confession)));
        $this->dispatchStatusEvents($confession, $originalStatus);

        return $confession;
    }

    /**
     * Check fingerprint.
     *
     * @param  array $attributes
     *
     * @return array
     */
    protected function checkFingerprint(array $attributes = [])
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
    protected function dispatchStatusEvents(Confession $confession, $originalStatus = '')
    {
        $newStatus = $confession->status;

        // Status change event should not trigger when confession is created.
        if (! empty($originalStatus) && $originalStatus !== $newStatus) {
            event(new ConfessionStatusWasChanged(
                $confession,
                $originalStatus,
                $this->resolveUser($confession)
            ));
        }

        // Call scheduled event even though the status is the same.
        if ($newStatus === 'Scheduled') {
            event(new ConfessionWasScheduled($confession, auth()->user()));

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
    protected function generateFingerprint()
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
    protected function schedule($confession, $status, $updateAt)
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
    protected function normalize(array $attributes)
    {
        $attributes['status_updated_at'] = Carbon::now();

        if (! empty($attributes['schedule'])) {
            $attributes['status_after'] = $attributes['status'];
            $attributes['status'] = 'Scheduled';

            $attributes['schedule'] = is_string($attributes['schedule']) ?
                Carbon::parse($attributes['schedule']) :
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
    protected function sync(Confession $confession, array $attributes = [])
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
            return Confession::findOrFail($confession);
        }

        return $confession;
    }

    /**
     * Resolves the user who modified the confession.
     *
     * @param  \NUSWhispers\Models\Confession $confession
     * @return \NUSWhispers\Models\User|null
     */
    protected function resolveUser(Confession $confession)
    {
        if (auth()->check()) {
            return auth()->user();
        }

        $lastLog = $confession
            ->logs()
            ->orderBy('created_on', 'desc')
            ->with(['user'])
            ->first();

        if (! $lastLog) {
            return;
        }

        return $lastLog->user;
    }
}

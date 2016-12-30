<?php

namespace NUSWhispers\Services;

use Carbon\Carbon;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Events\ConfessionWasDeleted;
use NUSWhispers\Events\ConfessionWasScheduled;
use NUSWhispers\Events\ConfessionWasUpdated;
use NUSWhispers\Models\Confession;

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

        event(new ConfessionWasDeleted($confession, auth()->user()));

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

        $confession = $this->resolve($confession);

        $originalStatus = $confession->status;

        $confession->update($attributes);
        $confession = $this->sync($confession, $attributes);

        event(new ConfessionWasUpdated($confession, auth()->user()));
        $this->dispatchStatusEvents($confession, $originalStatus);

        return $confession;
    }

    /**
     * Schedules a confession to change status in x hours.
     *
     * @param mixed $confession
     * @param string $status
     * @param int $hours
     *
     * @return \NUSWhispers\Models\Confession
     */
    protected function schedule($confession, $status = 'Approved', $hours = 1)
    {
        $confession = $this->resolve($confession);

        $confession->queue()->delete();
        $confession->queue()->create([
            'status_after' => $status,
            'update_status_at' => Carbon::now()->addHours($hours),
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
        $attributes['status'] = array_get($attributes, 'status', 'Pending');

        if (! empty($attributes['schedule'])) {
            $attributes['status_after'] = $attributes['status'];
            $attributes['status'] = 'Scheduled';
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
        $confession->categories()->sync(array_get($attributes, 'categories', []));

        if (! empty($attributes['schedule'])) {
            $this->schedule(
                $confession,
                $attributes['status_after'],
                (int) $attributes['schedule']
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
    protected function resolve($confession)
    {
        if (!$confession instanceof Confession) {
            return Confession::findOrFail($confession);
        }

        return $confession;
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

        // Call scheduled event even though the status is the same.
        if ($newStatus === 'Scheduled') {
            event(new ConfessionWasScheduled($confession, auth()->user()));
            return;
        }

        if ($originalStatus === $newStatus || $newStatus === 'Pending') {
            return;
        }

        $eventClass = '\NUSWhispers\Events\ConfessionWas' . $newStatus;
        event(new $eventClass($confession, auth()->user()));
    }
}

<?php

namespace NUSWhispers\Events;

use NUSWhispers\Models\User;
use NUSWhispers\Models\Confession;

class ConfessionStatusWasChanged extends BaseConfessionEvent
{
    /** @var string */
    public $originalStatus;

    /**
     * Create a new event instance.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param string $originalStatus
     * @param \NUSWhispers\Models\User|null  $user
     */
    public function __construct(Confession $confession, $originalStatus, User $user = null)
    {
        parent::__construct($confession, $user);
        $this->originalStatus = $originalStatus;
    }
}

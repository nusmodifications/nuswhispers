<?php

namespace NUSWhispers\Events;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\User;

class ConfessionWasDeleted
{
    /** @var \NUSWhispers\Models\Confession */
    public $confession;

    /** @var \NUSWhispers\Models\User */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param \NUSWhispers\Models\User|null  $user
     */
    public function __construct(Confession $confession, User $user = null)
    {
        $this->confession = $confession;
        $this->user = $user;
    }
}

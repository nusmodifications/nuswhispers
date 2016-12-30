<?php

namespace NUSWhispers\Events;

use Illuminate\Queue\SerializesModels;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\User;

class ConfessionStatusWasChanged
{
    use SerializesModels;

    /** @var \NUSWhispers\Models\Confession */
    public $confession;

    /** @var string */
    public $originalStatus;

    /** @var \NUSWhispers\Models\User */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param string $originalStatus
     * @param \NUSWhispers\Models\User|null  $user
     */
    public function __construct(Confession $confession, $originalStatus, User $user = null)
    {
        $this->confession = $confession;
        $this->originalStatus = $originalStatus;
        $this->user = $user;
    }
}

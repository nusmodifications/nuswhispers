<?php

namespace NUSWhispers\Events;

use Illuminate\Queue\SerializesModels;
use NUSWhispers\Models\Confession;

class ConfessionWasCreated
{
    use SerializesModels;

    /** @var \NUSWhispers\Models\Confession */
    public $confession;

    /**
     * Create a new event instance.
     *
     * @param \NUSWhispers\Models\Confession $confession
     */
    public function __construct(Confession $confession)
    {
        $this->confession = $confession;
    }
}

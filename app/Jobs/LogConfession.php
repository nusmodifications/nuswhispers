<?php

namespace NUSWhispers\Jobs;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\User;
use DateTime;

class LogConfession extends QueuedJob
{
    /**
     * Confession.
     * @var NUSWhispers\Models\Confession
     */
    protected $confession;

    /**
     * Confession status before it was saved.
     * @var string
     */
    protected $statusBefore = '';

    /**
     * User.
     * @var NUSWhispers\Models\User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param NUSWhispers\Models\User $user
     * @param string $statusBefore
     * @param NUSWhispers\Models\Confession $confession
     * @return void
     */
    public function __construct(User $user, string $statusBefore, Confession $confession)
    {
        $this->user = $user;
        $this->statusBefore = $statusBefore;
        $this->confession = $confession;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->confession->logs()->create([
            'confession_id' => $this->confession->getKey(),
            'status_before' => $this->statusBefore,
            'status_after' => $this->confession->status,
            'changed_by_user' => $this->user->getKey(),
            'created_on' => new DateTime(),
        ]);
    }
}

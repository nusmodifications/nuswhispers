<?php

namespace NUSWhispers\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use NUSWhispers\Models\Confession;
use NUSWhispers\Services\ConfessionService;

class PostScheduledConfessions extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Posts scheduled confessions.';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'confession:scheduled';

    /**
     * @var \NUSWhispers\Services\ConfessionService
     */
    protected $service;

    /**
     * Create a new command instance.
     *
     * @param \NUSWhispers\Services\ConfessionService $service
     */
    public function __construct(ConfessionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $confessions = Confession::query()
            ->orderBy('confession_queue.update_status_at', 'DESC')
            ->join('confession_queue', 'confessions.confession_id', '=', 'confession_queue.confession_id')
            ->where('confession_queue.update_status_at', '<=', Carbon::now()->toDateTimeString())
            ->get();

        $confessions->each(function (Confession $confession) {
            $queue = $confession->queue()->first();
            $this->comment('[INFO] Setting confession #' . $confession->confession_id . ' to ' . strtolower($queue->status_after) . '.');
            $this->service->updateStatus($confession, $queue->status_after);
            $confession->queue()->delete();
        });

        $this->comment('[INFO] Completed posting scheduled confessions.');
    }
}

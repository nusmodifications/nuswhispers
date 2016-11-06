<?php

namespace App\Console\Commands;

use App\Models\Confession;
use App\Repositories\ConfessionsRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PostScheduledConfessions extends Command
{
    /**
     * Confessions repository
     *
     * @var App\Repositories\ConfessionsRepository
     */
    protected $confessionsRepo;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ConfessionsRepository $confessionsRepo)
    {
        $this->confessionsRepo = $confessionsRepo;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $confessions = Confession::orderBy('confession_queue.update_status_at', 'DESC')
            ->join('confession_queue', 'confessions.confession_id', '=', 'confession_queue.confession_id')
            ->where('confession_queue.update_status_at', '<=', Carbon::now()->toDateTimeString())
            ->get();

        $confessions->each(function ($confession) {
            $queue = $confession->queue()->first();
            echo '[INFO] Setting confession #' . $confession->confession_id . ' to ' . strtolower($queue->status_after) . '.' . "\n";
            $this->confessionsRepo->switchStatus($confession, $queue->status_after);
            $confession->queue()->delete();
        });

        $this->comment('[INFO] Completed posting scheduled confessions.');
    }
}

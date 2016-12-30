<?php

namespace NUSWhispers\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class QueuedJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
}

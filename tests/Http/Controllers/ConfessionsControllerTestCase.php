<?php

namespace NUSWhispers\Tests\Http\Controllers;

use Mockery;
use NUSWhispers\Services\FacebookBatchProcessor;
use NUSWhispers\Tests\TestCase;

abstract class ConfessionsControllerTestCase extends TestCase
{
    /** @var \NUSWhispers\Services\FacebookBatchProcessor */
    protected $batchProcessor;

    public function setUp(): void
    {
        parent::setUp();

        $this->batchProcessor = Mockery::mock(FacebookBatchProcessor::class);

        $this->batchProcessor
            ->shouldReceive('processConfession')
            ->andReturnUsing(function ($confession) {
                return $confession;
            });

        $this->batchProcessor
            ->shouldReceive('processConfessions')
            ->andReturnUsing(function ($confessions) {
                return $confessions;
            });

        $this->app->bind(FacebookBatchProcessor::class, function () {
            return $this->batchProcessor;
        });
    }
}

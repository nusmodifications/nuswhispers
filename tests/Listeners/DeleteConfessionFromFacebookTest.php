<?php

namespace NUSWhispers\Tests\Listeners;

use Facebook\Facebook;
use Mockery;
use NUSWhispers\Events\ConfessionWasDeleted;
use NUSWhispers\Listeners\DeleteConfessionFromFacebook;
use NUSWhispers\Models\Confession;
use NUSWhispers\Tests\TestCase;

class DeleteConfessionFromFacebookTest extends TestCase
{
    protected $fb;

    protected $listener;

    public function setUp(): void
    {
        parent::setUp();

        $this->fb = Mockery::mock(Facebook::class);
        $this->listener = new DeleteConfessionFromFacebook($this->fb);

        $this->app['config']->set('services.facebook.page_id', 'nuswhispers');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testHandleUnpublishedConfession()
    {
        $confession = Confession::factory()->create([
            'fb_post_id' => '',
        ]);

        $this->fb->shouldNotReceive('delete');

        $this->listener->handle(new ConfessionWasDeleted($confession));
    }

    public function testHandleManualMode()
    {
        $this->app['config']->set('app.manual_mode', true);

        $confession = Confession::factory()->create([
            'fb_post_id' => '123',
        ]);

        $this->fb->shouldNotReceive('fb');

        $this->listener->handle(new ConfessionWasDeleted($confession));
    }

    public function testHandleApproved()
    {
        $confession = Confession::factory()->create([
            'fb_post_id' => '123',
            'status' => 'Approved',
        ]);

        $this->fb->shouldNotReceive('delete');

        $this->listener->handle(new ConfessionWasDeleted($confession));
    }

    public function testHandlePhoto()
    {
        $confession = Confession::factory()->create([
            'images' => 'foobar.jpg',
            'fb_post_id' => '123',
            'status' => 'Rejected',
        ]);

        $this->fb->shouldReceive('delete')->with('/123', [], Mockery::any());

        $this->listener->handle(new ConfessionWasDeleted($confession));

        $this->assertEmpty($confession->fb_post_id);
    }

    public function testHandleStatus()
    {
        $confession = Confession::factory()->create([
            'images' => '',
            'fb_post_id' => '123',
            'status' => 'Rejected',
        ]);

        $this->fb->shouldReceive('delete')->with('/nuswhispers_123', [], Mockery::any());

        $this->listener->handle(new ConfessionWasDeleted($confession));

        $this->assertEmpty($confession->fb_post_id);
    }
}

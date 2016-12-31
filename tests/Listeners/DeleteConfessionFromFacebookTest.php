<?php

namespace NUSWhispers\Tests\Listeners;

use Mockery;
use NUSWhispers\Tests\TestCase;
use NUSWhispers\Events\ConfessionWasDeleted;
use NUSWhispers\Listeners\DeleteConfessionFromFacebook;

class DeleteConfessionFromFacebookTest extends TestCase
{
    protected $fb;

    protected $listener;

    public function setUp()
    {
        parent::setUp();

        $this->fb = Mockery::mock('\SammyK\LaravelFacebookSdk\LaravelFacebookSdk');
        $this->listener = new DeleteConfessionFromFacebook($this->fb);

        $this->app['config']->set('services.facebook.page_id', 'nuswhispers');
    }

    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /** @test */
    public function testHandleUnpublishedConfession()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'fb_post_id' => '',
        ]);

        $this->fb->shouldNotReceive('delete');

        $this->listener->handle(new ConfessionWasDeleted($confession));
    }

    /** @test */
    public function testHandleManualMode()
    {
        $this->app['config']->set('app.manual_mode', true);

        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'fb_post_id' => '123',
        ]);

        $this->fb->shouldNotReceive('fb');

        $this->listener->handle(new ConfessionWasDeleted($confession));
    }

    /** @test */
    public function testHandleApproved()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'fb_post_id' => '123',
            'status' => 'Approved',
        ]);

        $this->fb->shouldNotReceive('delete');

        $this->listener->handle(new ConfessionWasDeleted($confession));
    }

    /** @test */
    public function testHandlePhoto()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'images' => 'foobar.jpg',
            'fb_post_id' => '123',
            'status' => 'Rejected',
        ]);

        $this->fb->shouldReceive('delete')->with('/123', [], Mockery::any());

        $this->listener->handle(new ConfessionWasDeleted($confession));

        $this->assertEmpty($confession->fb_post_id);
    }

    /** @test */
    public function testHandleStatus()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'images' => '',
            'fb_post_id' => '123',
            'status' => 'Rejected',
        ]);

        $this->fb->shouldReceive('delete')->with('/nuswhispers_123', [], Mockery::any());

        $this->listener->handle(new ConfessionWasDeleted($confession));

        $this->assertEmpty($confession->fb_post_id);
    }
}

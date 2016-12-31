<?php

namespace NUSWhispers\Tests\Listeners;

use Mockery;
use NUSWhispers\Tests\TestCase;
use NUSWhispers\Events\ConfessionWasApproved;
use NUSWhispers\Listeners\PostConfessionToFacebook;

class PostConfessionToFacebookTest extends TestCase
{
    protected $fb;

    protected $listener;

    public function setUp()
    {
        parent::setUp();

        $this->fb = Mockery::mock('\SammyK\LaravelFacebookSdk\LaravelFacebookSdk');
        $this->listener = new PostConfessionToFacebook($this->fb);

        $this->app['config']->set('services.facebook.page_id', 'nuswhispers');
    }

    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /** @test */
    public function testHandleManualMode()
    {
        $this->app['config']->set('app.manual_mode', true);

        $confession = factory(\NUSWhispers\Models\Confession::class)->create();

        $this->fb->shouldNotReceive('post');

        $this->listener->handle(new ConfessionWasApproved($confession));
    }

    /** @test */
    public function testHandleRejected()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('rejected')->create();

        $this->fb->shouldNotReceive('post');

        $this->listener->handle(new ConfessionWasApproved($confession));
    }

    /** @test */
    public function testHandleCreatePhoto()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'content' => 'Hello world!',
            'images' => 'abc.jpg',
            'status' => 'Approved',
            'fb_post_id' => '',
        ]);

        $responseMock = Mockery::mock('\Facebook\FacebookResponse');
        $responseMock->shouldReceive('getGraphNode')->andReturn(['id' => 123]);

        $this->fb->shouldReceive('post')
            ->with('/nuswhispers/photos', [
                'message' => $confession->getFacebookMessage(),
                'url' => 'abc.jpg',
            ], Mockery::any())
            ->andReturn($responseMock);

        $this->listener->handle(new ConfessionWasApproved($confession));

        $this->assertEquals('123', $confession->fb_post_id);
    }

    /** @test */
    public function testHandleUpdatePhoto()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'content' => 'Hello world!',
            'images' => 'abc.jpg',
            'status' => 'Approved',
            'fb_post_id' => '123',
        ]);

        $responseMock = Mockery::mock('\Facebook\FacebookResponse');
        $responseMock->shouldReceive('getGraphNode')->andReturn(['id' => 123]);

        $this->fb->shouldReceive('post')
            ->with('/123', [
                'message' => $confession->getFacebookMessage(),
                'url' => 'abc.jpg',
            ], Mockery::any())
            ->andReturn($responseMock);

        $this->listener->handle(new ConfessionWasApproved($confession));

        $this->assertEquals('123', $confession->fb_post_id);
    }

    /** @test */
    public function testHandleCreateStatus()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'content' => 'Hello world!',
            'images' => '',
            'status' => 'Approved',
            'fb_post_id' => '',
        ]);

        $responseMock = Mockery::mock('\Facebook\FacebookResponse');
        $responseMock->shouldReceive('getGraphNode')->andReturn(['id' => 'nuswhispers_123']);

        $this->fb->shouldReceive('post')
            ->with('/nuswhispers/feed', [
                'message' => $confession->getFacebookMessage(),
            ], Mockery::any())
            ->andReturn($responseMock);

        $this->listener->handle(new ConfessionWasApproved($confession));

        $this->assertEquals('123', $confession->fb_post_id);
    }

    /** @test */
    public function testHandleUpdateStatus()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'content' => 'Hello world!',
            'images' => '',
            'status' => 'Approved',
            'fb_post_id' => '123',
        ]);

        $responseMock = Mockery::mock('\Facebook\FacebookResponse');
        $responseMock->shouldReceive('getGraphNode')->andReturn(['id' => 'nuswhispers_123']);

        $this->fb->shouldReceive('post')
            ->with('/nuswhispers_123', [
                'message' => $confession->getFacebookMessage(),
            ], Mockery::any())
            ->andReturn($responseMock);

        $this->listener->handle(new ConfessionWasApproved($confession));

        $this->assertEquals('123', $confession->fb_post_id);
    }
}

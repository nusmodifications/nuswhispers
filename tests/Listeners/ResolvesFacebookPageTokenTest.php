<?php

namespace NUSWhispers\Tests\Listeners;

use NUSWhispers\Tests\TestCase;

class ResolvesFacebookPageTokenTest extends TestCase
{
    protected $stub;

    public function setUp(): void
    {
        parent::setUp();

        $this->stub = new ResolvesFacebookPageTokenStub();
        $this->app['config']->set('laravel-facebook-sdk.facebook_config.page_access_token', 'foobar');
    }

    /** @test */
    public function testResolvePageToken()
    {
        $user = factory(\NUSWhispers\Models\User::class)->create();
        $user->profiles()->create([
            'provider_name' => 'facebook',
            'page_token' => 'reaper',
        ]);

        $this->assertEquals('reaper', $this->stub->resolvePageToken($user));
    }

    /** @test */
    public function testResolvePageTokenEmptyToken()
    {
        $user = factory(\NUSWhispers\Models\User::class)->create();
        $user->profiles()->create([
            'provider_name' => 'facebook',
            'page_token' => '',
        ]);

        $this->assertEquals('foobar', $this->stub->resolvePageToken($user));
    }

    /** @test */
    public function testResolvePageTokenNull()
    {
        $this->assertEquals('foobar', $this->stub->resolvePageToken(null));
    }

    /** @test */
    public function testResolvePageTokenNoProfile()
    {
        $user = factory(\NUSWhispers\Models\User::class)->create();
        $this->assertEquals('foobar', $this->stub->resolvePageToken($user));
    }
}

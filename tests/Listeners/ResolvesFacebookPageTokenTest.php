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
        $this->app['config']->set('services.facebook.page_access_token', 'foobar');
    }

    public function testResolvePageToken()
    {
        $user = \NUSWhispers\Models\User::factory()->create();
        $user->profiles()->create([
            'provider_name' => 'facebook',
            'page_token' => 'reaper',
        ]);

        $this->assertEquals('reaper', $this->stub->resolvePageToken($user));
    }

    public function testResolvePageTokenEmptyToken()
    {
        $user = \NUSWhispers\Models\User::factory()->create();
        $user->profiles()->create([
            'provider_name' => 'facebook',
            'page_token' => '',
        ]);

        $this->assertEquals('foobar', $this->stub->resolvePageToken($user));
    }

    public function testResolvePageTokenNull()
    {
        $this->assertEquals('foobar', $this->stub->resolvePageToken(null));
    }

    public function testResolvePageTokenNoProfile()
    {
        $user = \NUSWhispers\Models\User::factory()->create();
        $this->assertEquals('foobar', $this->stub->resolvePageToken($user));
    }
}

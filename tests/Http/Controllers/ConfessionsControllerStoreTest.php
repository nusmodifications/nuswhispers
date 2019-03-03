<?php

namespace NUSWhispers\Tests\Http\Controllers;

use Carbon\Carbon;
use Mockery;
use NUSWhispers\Models\ApiKey;
use NUSWhispers\Tests\TestCase;
use ReCaptcha\ReCaptcha;
use ReCaptcha\Response;

class ConfessionsControllerStoreTest extends TestCase
{
    /** @var \ReCaptcha\ReCaptcha */
    protected $recaptcha;

    /** @var \ReCaptcha\Response */
    protected $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->response = Mockery::mock(Response::class);
        $this->recaptcha = Mockery::mock(ReCaptcha::class);

        $this->recaptcha->shouldReceive('verify')->andReturn($this->response);

        $this->app->bind('recaptcha', function () {
            return $this->recaptcha;
        });
    }

    public function testApiKeyFailed()
    {
        $this->json('POST', 'api/confessions', [
            'content' => 'Hello World!',
            'api_key' => '1234',
        ])
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }

    public function testApiKeySuccess()
    {
        $this->json('POST', 'api/confessions', [
            'content' => 'Hello World!',
            'api_key' => factory(ApiKey::class)->create()->key,
        ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function testLastUsedOn()
    {
        Carbon::setTestNow(Carbon::create(2017, 1, 1, 0, 0));
        $key = factory(ApiKey::class)->create();

        $this->json('POST', 'api/confessions', [
            'content' => 'Hello World!',
            'api_key' => $key->key,
        ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('api_keys', [
            'api_key_id' => $key->getKey(),
            'last_used_on' => Carbon::create(2017, 1, 1, 0, 0)->toDateTimeString(),
        ]);
    }

    public function testReCaptchaFailed()
    {
        $this->response->shouldReceive('isSuccess')->andReturn(false);

        $this->json('POST', 'api/confessions', [
            'content' => 'Hello World!',
            'captcha' => 'abc',
        ])
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }

    public function testReCaptchaSuccess()
    {
        $this->response->shouldReceive('isSuccess')->andReturn(true);

        $this->json('POST', 'api/confessions', [
            'content' => 'Hello World!',
            'captcha' => 'abc',
        ])
            ->assertStatus(200)
            ->assertJsonFragment(['success' => true]);
    }

    public function testStoreValidationFailed()
    {
        $this->json('POST', 'api/confessions')
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }
}

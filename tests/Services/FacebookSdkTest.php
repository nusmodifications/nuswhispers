<?php

namespace NUSWhispers\Tests\Services;

use Facebook\Facebook;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use NUSWhispers\Events\ConfessionWasApproved;
use NUSWhispers\Listeners\PostConfessionToFacebook;
use NUSWhispers\Models\Confession;
use NUSWhispers\Services\FacebookBatchProcessor;
use NUSWhispers\Tests\TestCase;

class FacebookSdkTest extends TestCase
{
    private MockHandler $http;

    public function testPostingUsesTheRealSdkAndParsesItsResponse(): void
    {
        $facebook = $this->facebookWithResponses([
            new Response(200, [], '{"id":"page_123"}'),
        ]);
        $confession = Confession::factory()->approved()->create([
            'images' => null,
            'fb_post_id' => null,
        ]);

        (new PostConfessionToFacebook($facebook))->handle(new ConfessionWasApproved($confession));

        $this->assertSame(123, $confession->fresh()->fb_post_id);
        $this->assertSame('application/x-www-form-urlencoded', $this->http->getLastRequest()->getHeaderLine('Content-Type'));
    }

    public function testBatchResponsesUseTheRealSdk(): void
    {
        $facebook = $this->facebookWithResponses([
            new Response(200, [], json_encode([[
                'code' => 200,
                'headers' => [],
                'body' => json_encode(['images' => [['source' => 'https://example.test/photo.jpg']]]),
            ]])),
        ]);
        $confession = Confession::factory()->approved()->create(['fb_post_id' => '123'])->fresh();

        $result = (new FacebookBatchProcessor($facebook))->processConfession($confession);

        $this->assertSame('https://example.test/photo.jpg', $result->images);
        $this->assertSame($confession->status_updated_at->timestamp, $result->getAttribute('status_updated_at_timestamp'));
    }

    public function testFacebookLoginHandlesSdkErrors(): void
    {
        $this->facebookWithResponses([
            new Response(400, [], json_encode(['error' => [
                'message' => 'Invalid access token',
                'type' => 'OAuthException',
                'code' => 190,
            ]])),
        ]);

        $this->postJson('/api/fbuser/login', ['fb_access_token' => 'invalid-token'])
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.0', 'Invalid access token');
    }

    private function facebookWithResponses(array $responses): Facebook
    {
        $this->http = new MockHandler($responses);
        $this->app->bind(Client::class, function ($app, array $parameters) {
            return new Client(array_replace($parameters['config'], [
                'handler' => HandlerStack::create($this->http),
            ]));
        });
        $this->app->forgetInstance('facebook');
        $facebook = $this->app->make(Facebook::class);

        return $facebook;
    }
}

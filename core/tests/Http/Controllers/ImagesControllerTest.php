<?php

namespace NUSWhispers\Tests\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Mockery;
use NUSWhispers\Services\ImgurService;
use NUSWhispers\Tests\TestCase;

class ImagesControllerTest extends TestCase
{
    public function testStore(): void
    {
        $service = Mockery::mock(ImgurService::class);
        $service->shouldReceive('upload')->andReturn('https://imgur.com/foo.jpg');

        $this->app->instance(ImgurService::class, $service);

        $this->json('POST', '/api/images', [
            'image' => UploadedFile::fake()->image('test.jpg'),
        ])->assertJsonFragment(['success' => true, 'url' => 'https://imgur.com/foo.jpg']);
    }

    public function testStoreMaxSize(): void
    {
        $this->json('POST', '/api/images', [
            'image' => UploadedFile::fake()->create('big.jpg', 20000),
        ])->assertStatus(422);
    }
}

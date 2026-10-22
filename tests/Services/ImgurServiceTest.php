<?php

namespace NUSWhispers\Tests\Services;

use Illuminate\Http\UploadedFile;
use NUSWhispers\Services\ImgurService;
use NUSWhispers\Tests\TestCase;

class ImgurServiceTest extends TestCase
{
    /**
     * @var \NUSWhispers\Services\ImgurService
     */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ImgurService::class);
    }

    public function testUpload(): void
    {
        $this->markTestSkipped();

        // @phpstan-ignore deadCode.unreachable (Live uploads are intentionally disabled.)
        $url = $this->service->upload(new UploadedFile(__DIR__ . '/test.jpg', 'test.jpg'));

        $this->assertStringStartsWith('https', $url);
    }
}

<?php

namespace NUSWhispers\Services;

use GuzzleHttp;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\UploadedFile;

class ImgurService
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Constructs an instance of ImgurService.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     */
    public function __construct(Config $config)
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'https://api.imgur.com/3/',
            'headers' => [
                'Authorization' => 'Client-ID ' . $config->get('services.imgur.client_id'),
            ],
        ]);
    }

    /**
     * Uploads an image to Imgur.
     *
     * @param \Illuminate\Http\UploadedFile $image
     *
     * @return string
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function upload(UploadedFile $image): string
    {
        $response = $this->client->request('POST', 'image', [
            'multipart' => [
                ['name' => 'image', 'contents' => $image->openFile('rb')],
            ],
        ]);

        $payload = json_decode((string) $response->getBody(), true);

        return array_get($payload, 'data.link');
    }
}

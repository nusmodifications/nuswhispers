<?php

namespace NUSWhispers\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use NUSWhispers\Services\ImgurService;

class ImagesController extends Controller
{
    /**
     * @var \NUSWhispers\Services\ImgurService
     */
    protected $service;

    /**
     * Constructs an instance of the controller.
     *
     * @param \NUSWhispers\Services\ImgurService $service
     */
    public function __construct(ImgurService $service)
    {
        $this->service = $service;
    }

    /**
     * Uploads an image and returns the URL.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'image' => 'required|image|max:10240',
        ]);

        return response()->json([
            'success' => true,
            'url' => $this->service->upload($request->file('image')),
        ]);
    }
}

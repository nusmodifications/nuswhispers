<?php

namespace NUSWhispers\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FrontendController extends Controller
{
    public function index(Request $request): BinaryFileResponse
    {
        $file = $request->path() === '/' ? 'app.html' : 'app-route.html';

        return response()->file(public_path($file), ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function pageData(string $path): BinaryFileResponse
    {
        abort_unless(str_ends_with($path, '.json'), 404);

        // Dynamic public URLs share Void's catch-all component and have no server props.
        // Never use the requested URL as a filesystem path.
        $file = $path === 'index.json' ? 'index.json' : 'home.json';

        return response()->file(public_path('_void/pages/' . $file), ['Content-Type' => 'application/json']);
    }
}

<?php

namespace NUSWhispers\Http\Controllers;

class ApiController extends Controller
{
    /**
     * Returns an empty response. This is used for OPTIONS call.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response('');
    }
}

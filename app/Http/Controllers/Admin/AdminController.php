<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Session;

/**
 * Abstract class for all admin controllers.
 */
abstract class AdminController extends Controller
{
    protected function flashMessage($message, $class = 'alert-success')
    {
        Session::flash('message', $message);
        Session::flash('alert-class', $class);
    }
}

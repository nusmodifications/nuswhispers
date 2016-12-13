<?php

namespace App\Http\Controllers\Admin;

use Session;
use App\Http\Controllers\Controller;

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

<?php

namespace NUSWhispers\Http\Controllers\Admin;

use NUSWhispers\Http\Controllers\Controller;

/**
 * Abstract class for all admin controllers.
 */
abstract class AdminController extends Controller
{
    /**
     * Flashes messages.
     *
     * @param string $message
     * @param string $class
     */
    protected function flashMessage($message, $class = 'alert-success')
    {
        session()->flash('message', $message);
        session()->flash('alert-class', $class);
    }
}

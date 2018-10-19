<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use NUSWhispers\Http\Controllers\Controller;
use Throwable;

abstract class AdminController extends Controller
{
    /**
     * Flashes messages.
     *
     * @param string $message
     * @param string $class
     *
     * @return void
     */
    protected function flashMessage($message, $class = 'alert-success'): void
    {
        session()->flash('message', $message);
        session()->flash('alert-class', $class);
    }

    /**
     * Redirects back to the previous page with success message.
     *
     * @param string $message
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function backWithSuccess(string $message): RedirectResponse
    {
        return back()
            ->with('message', $message)
            ->with('alert-class', 'alert-success');
    }

    /**
     * Redirects back to the previous page with error message.
     *
     * @param string $message
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function backWithError(string $message): RedirectResponse
    {
        return back()
            ->with('message', $message)
            ->with('alert-class', 'alert-danger');
    }

    /**
     * Wrapper function to handle errors if any exception occurs.
     *
     * @param callable $callback
     * @param string $prefix
     *
     * @return mixed
     */
    protected function withErrorHandling(callable $callback, string $prefix = '')
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            return $this->backWithError($prefix . $e->getMessage());
        }
    }
}

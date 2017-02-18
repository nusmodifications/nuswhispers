<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Search Engine Robot Routes
Route::get('/confession/{id}', function ($id) {
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    $botTypes = 'bot|crawl|slurp|spider|facebookexternalhit';

    $isCrawler = ! empty($userAgent) ? preg_match("/{$botTypes}/", $userAgent) > 0 : false;

    if ($isCrawler) {
        return App::make('NUSWhispers\Http\Controllers\RobotsController')->getConfession($id);
    } else {
        return File::get(public_path() . '/app.html');
    }
});

// Auth Routes
Route::group(['namespace' => 'Auth'], function () {
    $this->get('login', 'LoginController@showLoginForm')->name('login');
    $this->post('login', 'LoginController@login');
    $this->get('logout', 'LoginController@logout');
    $this->post('logout', 'LoginController@logout');

    $this->get('password/reset', 'ForgotPasswordController@showLinkRequestForm');
    $this->post('password/email', 'ForgotPasswordController@sendResetLinkEmail');

    $this->get('password/reset/{token}', 'ResetPasswordController@showResetForm');
    $this->post('password/reset', 'ResetPasswordController@reset');
});

// Mobile submit page
Route::get('/mobile_submit', function () {
    return File::get(public_path() . '/mobile_submit.html');
});

// Reroute everything else to angular
Route::get('/{getEverything?}/{all?}', function () {
    return File::get(public_path() . '/app.html');
});

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
        return App::make('NUSWhispers\Http\Controllers\FrontendController')->index(request());
    }
});

// Auth Routes
Auth::routes(['register' => false]);
Route::get('logout', 'Auth\LoginController@logout');

// Static Void navigation data for dynamic category, tag, search and confession URLs.
Route::get('/_void/pages/{path}', 'FrontendController@pageData')->where('path', '.*');

// Nginx serves generated assets directly; Laravel serves the public application shell.
Route::fallback('FrontendController@index');

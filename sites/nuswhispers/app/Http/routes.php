<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// API Routes
Route::group(array('prefix' => 'api'), function() {
    Route::get('confessions/popular', 'ConfessionsController@popular');
    Route::get('confessions/recent', 'ConfessionsController@recent');
    Route::get('confessions/category/{categoryId}', 'ConfessionsController@category');
    Route::get('confessions/tag/{tag}', 'ConfessionsController@tag');
    Route::get('confessions/search/{searchString}', 'ConfessionsController@search');
    Route::get('confessions/favourites', 'ConfessionsController@favourites');
	Route::resource('confessions', 'ConfessionsController',
		['only' => ['index', 'store', 'show']]);

	Route::resource('categories', 'CategoriesController',
		['only' => ['index', 'show']]);

    Route::get('tags/top/{num}', 'TagsController@topNTags');
    Route::resource('tags', 'TagsController',
        ['only' => ['index', 'show']]);

    Route::controllers([
        'fbuser' => 'FbUsersController',
    ]);
});

// Search Engine Robot Routes
Route::get('/confession/{id}', function($id) {
    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    $botTypes = 'bot|crawl|slurp|spider|facebookexternalhit';

    $isCrawler = !empty($userAgent) ? preg_match("/{$botTypes}/", $userAgent) > 0 : false;

    if ($isCrawler) {
        return App::make('\App\Http\Controllers\RobotsController')->getConfession($id);
    } else {
        return File::get(public_path() . '/app.html');
    }
});

// Auth Routes
Route::controllers([
	'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);

// Admin Routes
Route::controller('admin/users', 'Admin\UsersAdminController', [
    'postEdit' => 'admin.users.edit',
    'postAdd' => 'admin.users.add',
]);

Route::controller('admin/profile', 'Admin\ProfileController', [
    'postEdit' => 'admin.profile.edit',
]);

Route::controller('admin/confessions/comments', 'Admin\ModeratorCommentsAdminController');

Route::controller('admin/confessions', 'Admin\ConfessionsAdminController', [
    'postEdit' => 'admin.confessions.edit'
]);

// Temporarily redirect default admin to confessions dashboard
Route::get('/admin', function() {
    return redirect('admin/confessions');
});

// Mobile submit page
Route::get('/mobile_submit', function() {
    return File::get(public_path() . '/mobile_submit.html');
});

// reroute to angular
Route::get('/{getEverything?}/{all?}', function() {
    return File::get(public_path() . '/app.html');
});

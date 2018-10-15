<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the admin routes that are handled
| by your application. These will be protected by the `auth` middleware.
|
*/

// Redirect default admin to confessions dashboard.
Route::redirect('', 'admin/confessions');

Route::prefix('api-keys')->group(function () {
    Route::get('', 'ApiKeysAdminController@getIndex');
    Route::get('add', 'ApiKeysAdminController@getAdd');
    Route::get('delete/{id}', 'ApiKeysAdminController@getDelete');
});

Route::prefix('settings')->group(function () {
    Route::get('', 'SettingsAdminController@getIndex');
    Route::post('', 'SettingsAdminController@postIndex');
});

Route::prefix('users')->group(function () {
    Route::get('', 'UsersAdminController@getIndex');
    Route::get('add', 'UsersAdminController@getAdd');
    Route::post('add', 'UsersAdminController@postAdd');
    Route::get('edit/{id}', 'UsersAdminController@getEdit');
    Route::post('edit/{id}', 'UsersAdminController@postEdit');
    Route::get('delete/{id}', 'UsersAdminController@getDelete');
});

Route::prefix('profile')->group(function () {
    Route::get('', 'ProfileController@getIndex');
    Route::post('edit', 'ProfileController@postEdit');
    Route::get('connect/{provider}', 'ProfileController@getConnect');
    Route::get('delete/{provider}', 'ProfileController@getDelete');
});

Route::prefix('confessions')->group(function () {
    Route::get('comments/delete/{id}', 'ModeratorCommentsAdminController@getDelete');

    Route::get('edit/{id}', 'ConfessionsAdminController@getEdit');
    Route::post('edit/{id}', 'ConfessionsAdminController@postEdit');

    Route::get('approve/{id}/{hours?}', 'ConfessionsAdminController@getApprove');
    Route::get('feature/{id}/{hours?}', 'ConfessionsAdminController@getFeature');

    Route::get('unfeature/{id}', 'ConfessionsAdminController@getUnfeature');
    Route::get('reject/{id}', 'ConfessionsAdminController@getReject');
    Route::get('delete/{id}', 'ConfessionsAdminController@getDelete');

    Route::get('', 'ConfessionsAdminController@getIndex');
});

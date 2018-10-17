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
Route::redirect('', '/admin/confessions')->name('index');

Route::name('api-keys.')->prefix('api-keys')->group(function () {
    Route::get('', 'ApiKeysAdminController@getIndex')->name('index');
    Route::get('add', 'ApiKeysAdminController@getAdd')->name('add');

    Route::get('delete/{id}', 'ApiKeysAdminController@getDelete')
        ->name('delete');
});

Route::name('settings.')->prefix('settings')->group(function () {
    Route::get('', 'SettingsAdminController@getIndex')->name('index');
    Route::post('', 'SettingsAdminController@postIndex')->name('update');
});

Route::name('users.')->prefix('users')->group(function () {
    Route::get('', 'UsersAdminController@getIndex')->name('index');
    Route::get('add', 'UsersAdminController@getAdd')->name('create');
    Route::post('add', 'UsersAdminController@postAdd')->name('store');
    Route::get('edit/{user}', 'UsersAdminController@getEdit')->name('edit');
    Route::post('edit/{user}', 'UsersAdminController@postEdit')->name('update');
    Route::get('delete/{user}', 'UsersAdminController@getDelete')->name('delete');
});

Route::name('profile.')->prefix('profile')->group(function () {
    Route::get('', 'ProfileController@getIndex')->name('index');

    Route::post('edit', 'ProfileController@postEdit')->name('update');

    Route::get('connect/{provider}', 'ProfileController@getConnect')
        ->name('connect');

    Route::get('delete/{provider}', 'ProfileController@getDelete')
        ->name('unconnect');
});

Route::name('confessions.')->prefix('confessions')->group(function () {
    Route::get('comments/delete/{id}', 'ModeratorCommentsAdminController@getDelete')
        ->name('comments.delete');

    Route::get('edit/{confession}', 'ConfessionsAdminController@getEdit')
        ->name('edit');

    Route::post('edit/{confession}', 'ConfessionsAdminController@postEdit')
        ->name('update');

    Route::get('approve/{id}/{hours?}', 'ConfessionsAdminController@getApprove')
        ->name('approve');

    Route::get('feature/{id}/{hours?}', 'ConfessionsAdminController@getFeature')
        ->name('feature');

    Route::get('unfeature/{confession}', 'ConfessionsAdminController@getUnfeature')
        ->name('unfeature');

    Route::get('reject/{confession}', 'ConfessionsAdminController@getReject')
        ->name('reject');

    Route::get('delete/{confession}', 'ConfessionsAdminController@getDelete')
        ->name('delete');

    Route::get('', 'ConfessionsAdminController@getIndex')->name('index');
});

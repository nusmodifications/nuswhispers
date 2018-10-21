<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::name('confessions.')->prefix('confessions')->group(function () {
    Route::get('popular', 'ConfessionsController@popular')->name('popular');
    Route::get('recent', 'ConfessionsController@recent')->name('recent');
    Route::get('category/{categoryId}', 'ConfessionsController@category')->name('category');
    Route::get('tag/{tag}', 'ConfessionsController@tag')->name('tag');
    Route::get('search/{searchString}', 'ConfessionsController@search')->name('search');
    Route::get('favourites', 'ConfessionsController@favourites')->name('favourites');
});

Route::resource('confessions', 'ConfessionsController', ['only' => ['index', 'store', 'show']]);

Route::resource('categories', 'CategoriesController', ['only' => ['index', 'show']]);

Route::resource('images', 'ImagesController', ['only' => ['store']]);

Route::name('tags.')->prefix('tags')->group(function () {
    Route::get('top/{num}', 'TagsController@topNTags')->name('top');
});

Route::resource('tags', 'TagsController', ['only' => ['index', 'show']]);

Route::name('fbuser.')->prefix('fbuser')->group(function () {
    Route::post('login', 'FbUsersController@postLogin')->name('login');
    Route::post('logout', 'FbUsersController@postLogout')->name('logout');
    Route::post('favourite', 'FbUsersController@postFavourite')->name('favourite');
    Route::post('unfavourite', 'FbUsersController@postUnfavourite')->name('unfavourite');
});

Route::fallback('ApiController@index')->name('index');

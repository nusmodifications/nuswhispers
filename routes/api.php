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

Route::post('fbuser/login', 'FbUsersController@postLogin');
Route::post('fbuser/logout', 'FbUsersController@postLogout');
Route::post('fbuser/favourite', 'FbUsersController@postFavourite');
Route::post('fbuser/unfavourite', 'FbUsersController@postUnfavourite');

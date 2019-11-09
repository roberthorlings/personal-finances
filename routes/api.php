<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/categories/tree', 'CategoryController@tree');
Route::post('/transactions/import', 'TransactionController@import');
Route::post('/transactions/stats', 'TransactionController@generateStats');
Route::post('/accounts/stats', 'AccountController@generateStats');
Route::post('/categories/stats', 'CategoryController@generateStats');
Route::get('/categories/stats', 'CategoryController@stats');
Route::get('/categories/{category}/stats', 'CategoryController@categoryStats');
Route::apiResource('categories', 'CategoryController');
Route::apiResource('accounts', 'AccountController');
Route::apiResource('transactions', 'TransactionController');

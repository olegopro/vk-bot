<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::resource('/accounts',AccountController::class);
Route::resource('/tasks',TaskController::class);

Route::post('/account/task/{taskId}', [AccountController::class, 'accountByTaskId']);

Route::post('/account/data/{id}',[AccountController::class, 'getAccountData']);
Route::post('/account/followers/{id}',[AccountController::class, 'getAccountFollowers']);

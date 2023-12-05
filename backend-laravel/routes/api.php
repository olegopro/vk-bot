<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\SettingsController;
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


Route::prefix('tasks')->group(function () {
    Route::get('/{status?}', [TaskController::class, 'taskStatus']);
    Route::post('/task-info/{taskId}', [TaskController::class, 'taskInfo']);
    Route::post('/account/task/{taskId}', [AccountController::class, 'accountByTaskId']);
    Route::delete('/delete-like/{taskId}', [TaskController::class, 'deleteLike']);
    Route::delete('/delete-all-tasks/{status?}', [TaskController::class, 'deleteAllTasks']);
    Route::delete('/delete-task-by-id/{id}', [TaskController::class, 'deleteTaskById']);
});


Route::prefix('account')->group(function (){
    Route::post('/all-accounts', [AccountController::class, 'userAccounts']);
    Route::post('/task/{taskId}', [AccountController::class, 'accountByTaskId']);
    Route::post('/data/{id}', [AccountController::class, 'getAccountData']);
    Route::post('/followers/{id}', [AccountController::class, 'getAccountFollowers']);
    Route::post('/friends/{id}', [AccountController::class, 'getAccountFriends']);
    Route::post('/friends/count/{id}', [AccountController::class, 'getAccountCountFriends']);
    Route::post('/info/{access_token}', [AccountController::class, 'getAccountInfo']);
    Route::post('/add', [AccountController::class, 'setAccountData']);
    Route::post('/newsfeed', [AccountController::class, 'getAccountNewsfeed']);
    Route::post('/like', [AccountController::class, 'addLike']);
    Route::post('/get-posts-for-like', [AccountController::class, 'getNewsfeedPosts']);
    Route::post('/add-task-likes', [AccountController::class, 'addLikeTask']);
    Route::post('/get-screen-name-by-id', [AccountController::class, 'getScreenNameById']);
});


Route::post('/group/data/{id}', [AccountController::class, 'getGroupData']);


Route::prefix('settings')->group(function () {
    Route::post('/', [SettingsController::class, 'settings']);
    Route::post('/save', [SettingsController::class, 'saveSettings']);
});



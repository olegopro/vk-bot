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

Route::resource('/accounts', AccountController::class);


Route::prefix('tasks')->group(function () {
    // Ресурсные маршруты для задач
    Route::resource('/', TaskController::class);

    Route::post('/task-info/{taskId}', [TaskController::class, 'taskInfo']);

    Route::delete('/delete-like/{taskId}', [TaskController::class, 'deleteLike']);
    Route::delete('/delete-all-tasks', [TaskController::class, 'deleteAllTasks']);
    Route::delete('/delete-task-by-id/{id}', [TaskController::class, 'deleteTaskById']);
});


Route::post('/tasks/account/task/{taskId}', [AccountController::class, 'accountByTaskId']);
Route::post('/account/task/{taskId}', [AccountController::class, 'accountByTaskId']);


Route::post('/account/data/{id}', [AccountController::class, 'getAccountData']);
Route::post('/account/followers/{id}', [AccountController::class, 'getAccountFollowers']);
Route::post('/account/friends/{id}', [AccountController::class, 'getAccountFriends']);
Route::post('/account/friends/count/{id}', [AccountController::class, 'getAccountCountFriends']);
Route::post('/account/info/{access_token}', [AccountController::class, 'getAccountInfo']);
Route::post('/account/add', [AccountController::class, 'setAccountData']);
Route::post('/account/newsfeed', [AccountController::class, 'getAccountNewsfeed']);
Route::post('/account/like', [AccountController::class, 'addLike']);

Route::post('/account/get-posts-for-like', [AccountController::class, 'getNewsfeedPosts']);
Route::post('/account/add-task-likes', [AccountController::class, 'addLikeTask']);
Route::post('/account/get-screen-name-by-id', [AccountController::class, 'getScreenNameById']);

Route::post('/group/data/{id}', [AccountController::class, 'getGroupData']);


Route::post('/settings', [SettingsController::class, 'settings']);
Route::post('/settings/save', [SettingsController::class, 'saveSettings']);

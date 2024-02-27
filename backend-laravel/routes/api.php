<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CyclicTaskController;
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
    Route::get('/{status?}/{accountId?}', [TaskController::class, 'getTaskStatus']);
    Route::post('/task-info/{taskId}', [TaskController::class, 'getTaskInfo']);
    Route::delete('/delete-like/{taskId}', [TaskController::class, 'deleteLike']);
    Route::delete('/delete-all-tasks/{status?}/{accountId?}', [TaskController::class, 'deleteAllTasks']);
    Route::delete('/delete-task-by-id/{id}', [TaskController::class, 'deleteTaskById']);
});

Route::prefix('cyclic-tasks')->group(function () {
    Route::get('/', [CyclicTaskController::class, 'getCyclicTasks']);
    Route::post('/create-cyclic-task', [TaskController::class, 'createCyclicTask']);
    Route::patch('/pause-cyclic-task/{taskId}', [CyclicTaskController::class, 'pauseCyclicTask']);
    Route::delete('/delete-all-cyclic-tasks', [CyclicTaskController::class, 'deleteAllCyclicTasks']);
    Route::delete('/{taskId}', [CyclicTaskController::class, 'deleteCyclicTask']);
});

Route::prefix('account')->group(function () {
    Route::post('/all-accounts', [AccountController::class, 'fetchAllAccounts']);
    Route::post('/data/{id}', [AccountController::class, 'fetchAccountData']);
    Route::post('/followers/{id}', [AccountController::class, 'fetchAccountFollowers']);
    Route::post('/friends/{id}', [AccountController::class, 'fetchAccountFriends']);
    Route::post('/friends/count/{accountId}/{ownerId?}', [AccountController::class, 'fetchAccountCountFriends']);
    Route::post('/add', [AccountController::class, 'setAccountData']);
    Route::post('/newsfeed', [AccountController::class, 'fetchAccountNewsfeed']);
    Route::post('/like', [AccountController::class, 'addLike']);

    // Перенести в tasks
    Route::post('/get-posts-for-like', [TaskController::class, 'collectNewsfeedPostsForLikeTask']);
    Route::post('/add-task-likes', [TaskController::class, 'addLikeTaskToQueue']);
    Route::delete('/delete-account/{id}', [AccountController::class, 'deleteAccount']);
});

Route::post('/group/data/{id}', [AccountController::class, 'fetchGroupData']);

Route::prefix('settings')->group(function () {
    Route::post('/', [SettingsController::class, 'getSettings']);
    Route::post('/save', [SettingsController::class, 'saveSettings']);
});

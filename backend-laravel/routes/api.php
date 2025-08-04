<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\CyclicTaskController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticController;
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

Route::prefix('tasks')->group(function() {
    // Специфичные роуты должны быть ПЕРЕД общими роутами
    Route::get('/task-info/{taskId}', [TaskController::class, 'getTaskInfo']);
    Route::post('/get-posts-for-like', [TaskController::class, 'createAndQueueLikeTasksFromNewsfeed']);
    Route::post('/add-task-likes', [TaskController::class, 'processAndQueuePendingLikeTasks']);
    Route::delete('/delete-like/{taskId}', [TaskController::class, 'deleteLike']);
    Route::delete('/delete-all-tasks/{status?}/{accountId?}', [TaskController::class, 'deleteAllTasks']);
    Route::delete('/delete-task-by-id/{id}', [TaskController::class, 'deleteTaskById']);
    Route::post('/create-for-users', [TaskController::class, 'createLikeTasksForUserWallPosts']);
    
    // Общий роут ОБЯЗАТЕЛЬНО должен быть в самом конце
    Route::get('/{status?}/{accountId?}', [TaskController::class, 'getTasksByStatus']);
});

Route::prefix('cyclic-tasks')->group(function() {
    Route::get('/', [CyclicTaskController::class, 'getCyclicTasks']);
    Route::post('/create-cyclic-task', [CyclicTaskController::class, 'createCyclicTask']);
    Route::patch('/{taskId}', [CyclicTaskController::class, 'editCyclicTask']);
    Route::patch('/pause-cyclic-task/{taskId}', [CyclicTaskController::class, 'pauseCyclicTask']);
    Route::delete('/delete-all-cyclic-tasks', [CyclicTaskController::class, 'deleteAllCyclicTasks']);
    Route::delete('/{taskId}', [CyclicTaskController::class, 'deleteCyclicTask']);
});

Route::prefix('accounts')->group(function() {
    Route::get('/all-accounts', [AccountsController::class, 'fetchAllAccounts']);
    Route::post('/add-account', [AccountsController::class, 'setAccountData']);
    Route::delete('/delete-account/{id}', [AccountsController::class, 'deleteAccount']);
});

Route::prefix('account')->group(function() {
    Route::get('/data/{id}', [AccountController::class, 'fetchAccountData']);
    Route::get('/followers/{id}', [AccountController::class, 'fetchAccountFollowers']);
    Route::get('/friends/{id}', [AccountController::class, 'fetchAccountFriends']);
    Route::get('/friends/count/{accountId}/{ownerId?}', [AccountController::class, 'fetchAccountCountFriends']);
    Route::post('/newsfeed', [AccountController::class, 'fetchAccountNewsfeed']);
    Route::post('/like', [AccountController::class, 'addLike']);
});

Route::prefix('filters')->group(function() {
    // Поиск пользователей с применением фильтров
    Route::post('/search', [FilterController::class, 'searchUsers']);
    // Получение списка городов для фильтрации
    Route::post('/cities', [FilterController::class, 'getCities']);

    Route::post('/users-by-city', [FilterController::class, 'getUsersByCity']);
});
Route::get('/group/data/{id}', [AccountController::class, 'fetchGroupData']);

Route::get('/statistics', [StatisticController::class, 'getWeeklyTaskStats']);

Route::prefix('settings')->group(function() {
    Route::get('/', [SettingsController::class, 'getSettings']);
    Route::post('/save', [SettingsController::class, 'saveSettings']);
});

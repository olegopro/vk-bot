<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikesToPosts;
use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TaskRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TaskController extends Controller
{
    public function __construct(
        private readonly LoggingServiceInterface    $loggingService,
        private readonly VkClientService            $vkClient,
        private readonly TaskRepositoryInterface    $taskRepository,
        private readonly AccountRepositoryInterface $accountRepository,
        private readonly AccountController          $accountController
    ) {}

    // public function taskStatus($status = null, $accountId = null)
    // {
    //     $tasks = $this->taskRepository->taskStatus($status, $accountId);
    //
    //     return response()->json([
    //         'success' => true,
    //         'data'    => $tasks,
    //         'message' => 'Список задач получен'
    //     ]);
    // }

    public function taskStatus($status = null, $accountId = null)
    {
        // Проверяем, является ли первый параметр числом (accountId)
        if (is_numeric($status)) {
            $accountId = $status;
            $status = null;
        }

        $tasks = $this->taskRepository->taskStatus($status, $accountId);

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Список задач получен'
        ]);
    }

    public function taskInfo($taskId)
    {
        $taskData = $this->taskRepository->findTask($taskId);

        $ownerId = $taskData->owner_id;
        $postId = $taskData->item_id;
        $accountId = $taskData->account_id;

        $access_token = $this->accountRepository->getAccessTokenByAccountID($accountId);

        $postResponse = $this->vkClient->request('wall.getById', [
            'posts' => $ownerId . '_' . $postId,
        ], $access_token);

        $likesResponse = VkClient::getLikes($access_token, 'post', $ownerId, $postId);

        // Получение ID пользователей, которые поставили лайки
        $userIds = implode(',', $likesResponse['response']['items']);

        $usersResponse = VkClient::getUsers($userIds);

        // Деструктуризация 'response' для получения первого элемента
        list($response) = $postResponse['response'];

        $response['liked_users'] = $usersResponse['data']['response']; // Информация о пользователях
        $response['account_id'] = $accountId;

        return response()->json([
            'success' => true,
            'data'    => $response,
            'message' => 'Данные о задаче получены'
        ]);
    }

    public function getNewsfeedPosts(Request $request)
    {
        $account_id = $request->input('account_id');
        $task_count = $request->input('task_count');
        $access_token = VkClient::getAccessTokenByAccountID($account_id);

        $createdCount = 0; // Счетчик созданных записей
        $maxCreatedCount = $task_count; // Нужное количество записей
        $failedAttempts = 0; // Счетчик неудачных попыток

        do {
            $result = $this->accountController->getAccountNewsfeed($request)->getData(true);

            $data = $result['data']['response']['items'];
            $next_from = $result['data']['response']['next_from'];

            $attemptFailed = true; // Флаг, указывающий, что текущая попытка не удалась

            foreach ($data as $post) {
                if ($post['owner_id'] > 0
                    && !array_key_exists('copy_history', $post)
                    && $post['likes']['user_likes'] === 0
                    && isset($post['attachments'])
                    && collect($post['attachments'])->contains('type', 'photo')
                    && $createdCount < $maxCreatedCount
                ) {
                    $attemptFailed = false;

                    // Проверяем, нет ли уже такой задачи в процессе
                    $existingTask = Task::where('owner_id', $post['owner_id'])
                                        ->where('item_id', $post['post_id'])
                                        ->first();

                    if ($existingTask) {
                        continue; // Пропускаем, если такая задача уже существует
                    }

                    $username = $this->vkClient->request('users.get', [
                        'fields'  => 'screen_name',
                        'user_id' => $post['owner_id']
                    ]);

                    usleep(300000);

                    Task::create([
                        'account_id'    => $account_id,
                        'first_name'    => $username['response'][0]['first_name'],
                        'last_name'     => $username['response'][0]['last_name'],
                        'owner_id'      => $post['owner_id'],
                        'item_id'       => $post['post_id'],
                        'attempt_count' => 1,
                        'status'        => 'pending'
                    ]);

                    $createdCount++;

                    if ($createdCount >= $maxCreatedCount) {
                        break 2;
                    }
                }
            }

            if ($attemptFailed) {
                $failedAttempts++;
            } else {
                $failedAttempts = 0;
            }

            $request->merge(['start_from' => $next_from]);

            // Проверка на не выполнение условия 10 раза
            if ($failedAttempts >= 10) {
                break;
            }

        } while ($createdCount < $maxCreatedCount && !empty($next_from));

        return $this->addLikeTask($access_token);
    }

    public function addLikeTask($token)
    {
        $increase = $pause = DB::table('settings')
                               ->where('id', '=', '1')
                               ->value('task_timeout');

        $tasks = DB::table('tasks')
                   ->where('status', '=', 'pending')
                   ->get();

        foreach ($tasks as $task) {
            $run_at = now()->addSeconds($pause);

            // Обновляем время запуска и статус задачи
            DB::table('tasks')
              ->where('id', $task->id)
              ->update([
                  'run_at' => $run_at,
                  'status' => 'queued' // Обновляем статус на 'queued'
              ]);

            // Затем отправляем задачу в очередь
            addLikesToPosts::dispatch($task, $token, $this->loggingService)
                                  ->delay(now()->addSeconds($pause));

            $pause += $increase;
        }

        // Перезапрашиваем задачи из базы данных перед отправкой в ответе
        $tasks = DB::table('tasks')
                   ->where('status', '=', 'pending')
                   ->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Задачи успешно созданы'
        ]);
    }

    public function deleteAllTasks($status = null, $accountId = null)
    {
        $this->taskRepository->clearQueueBasedOnStatus($status, $accountId);

        return response()->json([
            'success' => true,
            'message' => 'Задачи успешно удалены'
        ]);
    }

    public function deleteTaskById($id)
    {
        $taskStatus = $this->taskRepository->getTaskStatusById($id);

        switch ($taskStatus) {
            case 'done':
                $this->taskRepository->deleteCompletedTask($id);
                break;

            case 'queued':
                $this->taskRepository->deleteQueuedTask($id);
                break;

            case 'failed':
                $this->taskRepository->deleteFailedTask($id);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Задача с id = $id удалена"
        ]);
    }

    public function deleteLike($taskId)
    {
        $taskData = $this->taskRepository->findTask($taskId);

        if (!$taskData) {
            return ['success' => false, 'error' => 'Задача не найдена'];
        }

        $accessToken = $this->accountRepository->getAccessTokenByAccountID($taskData->account_id);

        return response()->json(
            VkClient::deleteLike(
                $accessToken,
                'post',
                $taskData->owner_id,
                $taskData->item_id
            )
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Library\VkClient;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    public function index($status = null)
    {
        $query = Task::query();

        if ($status) {
            $query->where('status', $status);
        }

        $tasks = $query->get();

        return response($tasks);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function taskInfo($taskId)
    {
        $taskData = Task::find($taskId);

        if (!$taskData) {
            return response()->json(['error' => 'Задача не найдена'], 404);
        }

        $ownerId = $taskData->owner_id;
        $postId = $taskData->item_id;

        $access_token = $this->getAccessTokenByAccountID($taskData->account_id);

        $postResponse = (new VkClient($access_token))->request('wall.getById', [
            'posts' => $ownerId . '_' . $postId,
        ]);

        $likesResponse = $this->getLikes($access_token, 'post', $ownerId, $postId);

        // Получение ID пользователей, которые поставили лайки
        $userIds = implode(',', $likesResponse['response']['items']);

        // Получение информации о пользователях
        $usersResponse = (new VkClient($access_token))->request('users.get', [
            'user_ids' => $userIds,
        ]);

        // Деструктуризация 'response' для получения первого элемента
        list($response) = $postResponse['response'];

        $response['liked_users'] = $usersResponse['response']; // Информация о пользователях
        $response['account_id'] = $taskData->account_id;

        return response()->json(['response' => $response]);
    }

    public function likedUsersPost() {}

    // public function deleteAllTasks()
    // {
    //     DB::table('tasks')->truncate(); // Очистка таблицы tasks
    //     DB::table('jobs')->truncate();  // Очистка таблицы jobs
    //
    //     return response()->json(['message' => 'All tasks have been deleted']);
    // }

    public function deleteAllTasks($status = null) {
        // Проверяем, указан ли статус
        if ($status) {
            // Удаляем задачи с указанным статусом
            Task::where('status', $status)->delete();
        } else {
            // Удаляем все задачи, если статус не указан
            Task::truncate();
        }

        $this->clearQueueBasedOnStatus($status);

        return response()->json(['message' => 'Tasks deleted successfully']);
    }

    public function deleteTaskById($id)
    {
        // Получить статус задачи
        $taskStatus = DB::table('tasks')->where('id', $id)->value('status');

        // Если задача уже выполнена
        if ($taskStatus === 'done') {
            // Удаление задачи из таблицы tasks
            $this->deleteCompletedTask($id);
        }

        // Удаление задачи из очереди
        if ($taskStatus === 'pending') {
            $this->deletePendingTask($id);
        }

        // Если задача не выполнена (failed)
        if ($taskStatus === 'failed') {
            // Выполнить действия для удаления задачи с статусом failed
            $this->deleteFailedTask($id);
        }
    }

    public function deleteLike($taskId)
    {
        $taskData = Task::find($taskId);

        if (!$taskData) {
            return response()->json(['error' => 'Задача не найдена'], 404);
        }

        $ownerId = $taskData->owner_id;
        $postId = $taskData->item_id;

        $access_token = $this->getAccessTokenByAccountID($taskData->account_id);

        return (new VkClient($access_token))->request('likes.delete', [
            'type'     => 'post',
            'owner_id' => $ownerId,
            'item_id'  => $postId
        ]);
    }

    protected function clearQueueBasedOnStatus($status = null) {
        if ($status === 'done') {
            // Удаляем только из таблицы tasks
            Task::where('status', 'done')->delete();

        } elseif ($status === 'pending') {
            // Удаляем из таблицы tasks
            Task::where('status', 'pending')->delete();

            // Удаляем также из таблицы jobs
            $jobs = DB::table('jobs')->get();
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $command = unserialize($payload['data']['command']);

                // Используем новый метод getTaskStatus() для получения статуса задачи
                if (method_exists($command, 'getTaskStatus')) {
                    $taskStatus = $command->getTaskStatus();

                    if (Str::lower($taskStatus) === 'pending') {
                        DB::table('jobs')->where('id', $job->id)->delete();
                    }
                }
            }

        } elseif ($status === null) {
            // Удаляем все задачи из tasks
            Task::truncate();

            // Удаляем все задачи из jobs
            DB::table('jobs')->truncate();
        }
    }

    private function deletePendingTask(int $taskId)
    {
        DB::table('jobs')->orderBy('id')->chunk(100, function ($jobs) use ($taskId) {
            foreach ($jobs as $job) {
                // Декодирование поля payload
                $payload = json_decode($job->payload, true);

                // Десериализация поля command
                $command = unserialize($payload['data']['command']);

                // Получение идентификатора задачи из объекта команды
                $commandTaskId = $command->getTask()->id;

                // Если идентификатор задачи соответствует идентификатору задачи, который нужно удалить
                if ($commandTaskId === $taskId) {
                    // Удаление задачи из очереди
                    DB::table('jobs')->where('id', $job->id)->delete();

                    // Удаление задачи из таблицы tasks
                    DB::table('tasks')->where('id', $taskId)->delete();
                }
            }
        });

        return response()->json(['message' => 'Задача была удалена']);
    }

    private function deleteCompletedTask(int $taskId)
    {
        DB::table('tasks')->where('id', $taskId)->delete();

        return response()->json(['message' => 'Завершенная задача была удалена']);
    }

    private function deleteFailedTask(int $taskId)
    {
        // Удаление задачи из таблицы tasks
        DB::table('tasks')->where('id', $taskId)->delete();

        return response()->json(['message' => 'Невыполненная задача была удалена']);
    }

    private function getAccessTokenByAccountID($account_id)
    {
        return $account = DB::table('accounts')
                            ->where('account_id','=',$account_id)
                            ->value('access_token');
    }

    private function getLikes($access_token, $type, $owner_id, $item_id)
    {
        return (new VkClient($access_token))->request('likes.getList', [
            'type'     => $type,
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ]);
    }
}

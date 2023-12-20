<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\VkClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class TaskController extends Controller
{
	protected $vkClient;

	public function __construct(VkClient $vkClient)
	{
		$this->vkClient = $vkClient;
	}

	public function taskStatus($status = null)
    {
        $query = Task::query();

        if ($status) {
            $query->where('status', $status);
        }

        $tasks = $query->get();

        return response()->json($tasks);
    }

    public function taskInfo($taskId)
    {
        $taskData = Task::find($taskId);

        // Или использовать неявный findOrFail
        if (!$taskData) {
            return response()->json(['error' => 'Задача не найдена'], 404);
        }

        $ownerId = $taskData->owner_id;
        $postId = $taskData->item_id;

        $access_token = $this->getAccessTokenByAccountID($taskData->account_id);

        $postResponse = $this->vkClient->request('wall.getById', [
            'posts' => $ownerId . '_' . $postId,
        ]);

        $likesResponse = $this->getLikes($access_token, 'post', $ownerId, $postId);

        // Получение ID пользователей, которые поставили лайки
        $userIds = implode(',', $likesResponse['response']['items']);

        // Получение информации о пользователях
        $usersResponse = $this->vkClient->request('users.get', [
            'user_ids' => $userIds,
        ]);

        // Деструктуризация 'response' для получения первого элемента
        list($response) = $postResponse['response'];

        $response['liked_users'] = $usersResponse['response']; // Информация о пользователях
        $response['account_id'] = $taskData->account_id;

        return response()->json(['response' => $response]);
    }

    public function deleteAllTasks($status = null)
    {
        // Проверяем, указан ли статус
        if ($status) {
            // Удаляем задачи с указанным статусом
            Task::where('status', $status)->delete();
        } else {
            // Удаляем все задачи, если статус не указан
            Task::truncate();
        }

        if ($status === 'failed') {
            DB::table('failed_jobs')->truncate();
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
            // Выполнить действия для удаления задачи со статусом failed
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

        return $this->vkClient->request('likes.delete', [
            'type'     => 'post',
            'owner_id' => $ownerId,
            'item_id'  => $postId
        ]);
    }

    private function clearQueueBasedOnStatus($status = null)
    {
        switch ($status) {
            case 'done':
                // Удаляем только из таблицы tasks
                Task::where('status', 'done')->delete();
                break;

            case 'pending':
                // Удаляем из таблицы tasks
                Task::where('status', 'pending')->delete();

                // Удаляем также из таблицы jobs
                $jobs = DB::table('jobs')->get();
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $command = unserialize($payload['data']['command']);

                    if (method_exists($command, 'getTaskStatus')) {
                        $taskStatus = $command->getTaskStatus();

                        if (Str::lower($taskStatus) === 'pending') {
                            DB::table('jobs')->where('id', $job->id)->delete();
                        }
                    }
                }
                break;

            case 'failed':
                // Удаляем задачи со статусом failed из таблицы tasks
                Task::where('status', 'failed')->delete();
                // Очищаем таблицу failed_jobs
                DB::table('failed_jobs')->truncate();
                break;

            default:
                // Удаляем все задачи из tasks и jobs
                Task::truncate();
                DB::table('jobs')->truncate();
                break;
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
                            ->where('account_id', '=', $account_id)
                            ->value('access_token');
    }

    private function getLikes($access_token, $type, $owner_id, $item_id)
    {
        return $this->vkClient->request('likes.getList', [
            'type'     => $type,
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ]);
    }
}

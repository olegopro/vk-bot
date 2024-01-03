<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TaskRepositoryInterface;
use App\Services\VkClient;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class TaskController extends Controller
{
	protected $vkClient;
	protected $taskRepository;
	protected $accountRepository;

	public function __construct(VkClient $vkClient, TaskRepositoryInterface $taskRepository, AccountRepositoryInterface $accountRepository)
	{
		$this->vkClient = $vkClient;
		$this->taskRepository = $taskRepository;
		$this->accountRepository = $accountRepository;
	}

	public function taskStatus($status = null)
	{
		$tasks = $this->taskRepository->taskStatus($status);

		return response()->json($tasks);
	}

	public function taskInfo($taskId)
	{
		$taskData = $this->taskRepository->findTask($taskId);

		$ownerId = $taskData->owner_id;
		$postId = $taskData->item_id;

		$access_token = $this->accountRepository->getAccessTokenByAccountID($taskData->account_id);

		$postResponse = $this->vkClient->request('wall.getById', [
			'posts' => $ownerId . '_' . $postId,
		], $access_token);

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
		$this->taskRepository->deleteAllTasks($status);

		if ($status === 'failed') {
			DB::table('failed_jobs')->truncate();
		}

		$this->taskRepository->clearQueueBasedOnStatus($status);

		return response()->json(['message' => 'Tasks deleted successfully']);
	}

	public function deleteTaskById($id)
	{
		$taskStatus = $this->taskRepository->getTaskStatusById($id);

		switch ($taskStatus) {
			case 'done':
				$this->taskRepository->deleteCompletedTask($id);
				break;

			case 'pending':
				$this->taskRepository->deletePendingTask($id);
				break;

			case 'failed':
				$this->taskRepository->deleteFailedTask($id);
				break;
		}

		return response()->json(['message' => 'Task deleted successfully']);
	}

	public function deleteLike($taskId)
	{
		$taskData = $this->taskRepository->findTask($taskId);
		$access_token = $this->accountRepository->getAccessTokenByAccountID($taskData->account_id);

		if (!$taskData) {
			return response()->json(['error' => 'Задача не найдена'], 404);
		}

		return $this->vkClient->request('likes.delete', [
			'type'     => 'post',
			'owner_id' => $taskData->owner_id,
			'item_id'  => $taskData->item_id
		], $access_token);
	}

	private function getLikes($access_token, $type, $owner_id, $item_id)
	{
		return $this->vkClient->request('likes.getList', [
			'type'     => $type,
			'owner_id' => $owner_id,
			'item_id'  => $item_id
		], $access_token);
	}
}

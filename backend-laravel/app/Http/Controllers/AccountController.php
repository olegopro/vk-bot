<?php

namespace App\Http\Controllers;

use App\Jobs\addLikesToPosts;
use App\Models\Account;
use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClient;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class AccountController extends Controller
{
	private $loggingService;
	private $accountRepository;
	protected $vkClient;

	public function __construct(LoggingServiceInterface $loggingService, VkClient $vkClient, AccountRepositoryInterface $accountRepository)
	{
		$this->loggingService = $loggingService;
		$this->accountRepository = $accountRepository;
		$this->vkClient = $vkClient;
	}

	public function allAccounts(Request $request)
	{
		return $this->accountRepository->getAllAccounts();
	}

	public function deleteAccount($id)
	{
		$this->accountRepository->deleteAccount($id);
	}

	public function getAccountData($ids)
	{
		if (is_array($ids)) {
			$ids = implode(',', $ids);
		}

		return $this->vkClient->request('users.get', [
			'user_ids' => $ids,
			'fields'   => [
				'photo_200',
				'status',
				'screen_name',
				'last_seen',
				'followers_count',
				'city',
				'online',
				'bdate',
				'country',
				'sex'
			]
		]);
	}

	public function getGroupData($id)
	{
		return $this->vkClient->request('groups.getById', [
			'group_id' => $id,
			'fields'   => [
				'photo_200',
				'screen_name',
				'country',
				'description',
				'members_count',
				'status',
				'activity',
				'city',
			]
		]);
	}

	public function getAccountFollowers($id, $limit = 6)
	{
		return $this->vkClient->request('users.getFollowers', [
			'user_id' => $id,
			'count'   => $limit,
			'fields'  => [
				'about',
				'photo_200'
			]
		]);
	}

	public function getAccountFriends($id, $limit = 6)
	{
		return $this->vkClient->request('friends.get', [
			'user_id' => $id,
			'count'   => $limit,
			'fields'  => [
				'about',
				'photo_200'
			]
		]);
	}

	public function getAccountCountFriends($accountId, $ownerId)
	{
		$accessToken = $this->accountRepository->getAccessTokenByAccountID($accountId);
		$result = $this->vkClient->request('friends.get', [
			'user_id' => $ownerId,
			'count'   => 1,
		], $accessToken);

		$response = [
			'response' => [
				'id'    => $ownerId,
				'count' => $result['response']['count'],
			]
		];

		return response()->json($response);
	}

	public function getAccountInfo($access_token)
	{
		return $this->vkClient->request('account.getProfileInfo', [], $access_token);
	}

	public function setAccountData(Request $request)
	{
		$accountData = $this->getAccountInfo($request['access_token']);

		return $this->accountRepository->createAccount([
			'access_token' => $request['access_token'],
			'account_id'   => $accountData['response']['id'],
			'screen_name'  => $accountData['response']['screen_name'],
			'first_name'   => $accountData['response']['first_name'],
			'last_name'    => $accountData['response']['last_name'],
			'bdate'        => $accountData['response']['bdate']
		]);
	}

	public function getAccountNewsfeed(Request $request)
	{
		$access_token = $this->getAccessTokenByAccountID($request->input('account_id'));
		$screen_name = $this->getScreenNameByToken($access_token);

		// Логирование запроса
		$this->loggingService->log(
			'account_newsfeed',
			$screen_name,
			'VK API Request',
			['request' => $request]
		);

		$response = $this->vkClient->request('newsfeed.get', [
			'filters'    => 'post',
			'count'      => 40,
			'start_from' => $request->input('start_from') ?? null
		], $access_token);

		// Логирование ответа
		$this->loggingService->log(
			'account_newsfeed',
			$screen_name,
			'VK API Response',
			['response' => $response]
		);

		return $response;
		// return response()->json($response);
	}

	public function getNewsfeedPosts(Request $request)
	{
		$account_id = $request->input('account_id');
		$task_count = $request->input('task_count');
		$access_token = $this->getAccessTokenByAccountID($account_id);

		$createdCount = 0; // Счетчик созданных записей
		$maxCreatedCount = $task_count; // Нужное количество записей
		$failedAttempts = 0; // Счетчик неудачных попыток

		do {
			$result = $this->getAccountNewsfeed($request);

			$data = $result['response']['items'];
			$next_from = $result['response']['next_from'];

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

			// Проверка на не выполнение условия 3 раза
			if ($failedAttempts >= 3) {
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

			// Сохраняем время запуска задачи в базе данных
			DB::table('tasks')
			  ->where('id', $task->id)
			  ->update(['run_at' => $run_at]);

			// Затем отправляем задачу в очередь
			addLikesToPosts::dispatch($task, $token, $this->loggingService)
			               ->delay(now()->addSeconds($pause));

			$pause += $increase;
		}

		// Перезапрашиваем задачи из базы данных перед отправкой в ответе
		$tasks = DB::table('tasks')
		           ->where('status', '=', 'pending')
		           ->get();

		return response()->json($tasks);
	}

	public function addLike(Request $request)
	{
		$owner_id = $request->input('owner_id');
		$item_id = $request->input('item_id');

		$access_token = $this->getAccessTokenByAccountID(request()->input('account_id'));
		$screen_name = $this->getScreenNameByToken($access_token);

		// Логирование запроса
		$this->loggingService->log(
			'account_like',
			$screen_name,
			'VK API Request',
			[
				'request' => [
					'token' => $access_token,
					'task'  => [
						'owner_id' => $owner_id,
						'item_id'  => $item_id,
					],
				]
			]
		);

		$response = $this->vkClient->request('likes.add', [
			'type'     => 'post',
			'owner_id' => $owner_id,
			'item_id'  => $item_id
		], $access_token);

		// Логирование ответа
		$this->loggingService->log(
			'account_like',
			$screen_name,
			'VK API Response',
			['response' => $response]
		);

		return response()->json($response);
	}

	public function getScreenNameById(Request $request)
	{
		$user_id = $request->input('user_id');

		return $this->vkClient->request('users.get', [
			'fields'  => 'screen_name',
			'user_id' => $user_id
		]);
	}

	private function getAccessTokenByAccountID($account_id)
	{
		return $this->accountRepository->getAccessTokenByAccountID($account_id);
	}

	private function getScreenNameByToken($access_token)
	{
		return $this->accountRepository->getScreenNameByToken($access_token);
	}
}

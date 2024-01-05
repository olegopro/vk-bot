<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikesToPosts;
use App\Models\Account;
use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class AccountController extends Controller
{
    private $loggingService;
    private $accountRepository;
    protected $vkClient;

    public function __construct(LoggingServiceInterface $loggingService, VkClientService $vkClient, AccountRepositoryInterface $accountRepository)
    {
        $this->loggingService = $loggingService;
        $this->accountRepository = $accountRepository;
        $this->vkClient = $vkClient;
    }

    public function allAccounts(Request $request)
    {
        $accounts = $this->accountRepository->getAllAccounts();

        return response()->json([
            'success' => true,
            'data'    => $accounts,
            'message' => 'Список аккаунтов получен'
        ]);
    }

    public function deleteAccount($id)
    {
        $this->accountRepository->deleteAccount($id);

        return response()->json([
            'success' => true,
            'message' => 'Аккаунт удален'
        ]);
    }

    public function getAccountData($ids)
    {
        return response()->json(VkClient::getAccountData($ids));
    }

    public function getGroupData($id)
    {
        return response()->json(VkClient::getGroupData($id));
    }

    public function getAccountFollowers($id, $limit = 6)
    {
        return response()->json(VkClient::getAccountFollowers($id, $limit));
    }

    public function getAccountFriends($id, $limit = 6)
    {
        return response()->json(VkClient::getAccountFriends($id, $limit));
    }

    public function getAccountCountFriends($accountId, $ownerId)
    {
        $accessToken = $this->accountRepository->getAccessTokenByAccountID($accountId);
        $response = VkClient::getAccountCountFriends($accountId, $ownerId, $accessToken);

        return response()->json($response);
    }

    public function getAccountInfo($access_token)
    {
        return response()->json(VkClient::getAccountInfo($access_token));
    }

    public function setAccountData(Request $request)
    {
        return response()->json(VkClient::setAccountData($request['access_token'], $this->accountRepository));
    }

    public function getAccountNewsfeed(Request $request)
    {
        return response()->json(
            VkClient::getAccountNewsfeed(
                $request->input('account_id'),
                $request->input('start_from'),
                $this->loggingService
            )
        );
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
            $result = $this->getAccountNewsfeed($request)->getData(true);

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

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Задачи успешно созданы'
        ]);
    }

    public function addLike(Request $request)
    {
        $ownerId = $request->input('owner_id');
        $itemId = $request->input('item_id');
        $accountId = $request->input('account_id');
        $accessToken = $this->vkClient->getAccessTokenByAccountID($accountId);

        return response()->json(
            VkClient::addLike(
                $ownerId,
                $itemId,
                $accessToken,
                $this->loggingService
            )
        );
    }

    // TODO: Нигде не используется
    public function getScreenNameById(Request $request)
    {
        $user_id = $request->input('user_id');

        $response = $this->vkClient->request('users.get', [
            'fields'  => 'screen_name',
            'user_id' => $user_id
        ]);

        return response()->json([
            'success' => true,
            'data'    => $response,
            'message' => 'Отображаемое имя получено'
        ]);
    }
}

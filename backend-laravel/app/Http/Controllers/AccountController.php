<?php

namespace App\Http\Controllers;

use App\Jobs\addLikesToPosts;
use App\Library\VkClient;
use App\Models\Account;
use App\Models\Task;
use App\Services\LoggingService;
use App\Services\LoggingServiceInterface;
use DB;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    private $loggingService;

    public function __construct(LoggingServiceInterface $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $accounts = Account::all();

        return response($accounts);
    }

    public function accountByTaskId($taskId)
    {
        $account = Task::find($taskId)->account;

        return response($account);
    }

    public function show($id)
    {
        $account = Account::find($id);

        return response($account);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        return Account::destroy($id);
    }

    public function getAccountData($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        return (new VkClient())->request('users.get', [
            'user_ids' => $ids,
            'fields'   => [
                'photo_200',
                'status',
                'screen_name',
                'last_seen',
                'followers_count',
                'city',
                'online'
            ]
        ]);
    }

    public function getGroupData($id)
    {
        return (new VkClient())->request('groups.getById', [
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
        return (new VkClient())->request('users.getFollowers', [
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
        return (new VkClient())->request('friends.get', [
            'user_id' => $id,
            'count'   => $limit,
            'fields'  => [
                'about',
                'photo_200'
            ]
        ]);
    }

    public function getAccountCountFriends($id)
    {
        $result = (new VkClient())->request('friends.get', [
            'user_id' => $id,
            'count'   => 1,
        ]);

        $response = [
            'response' => [
                'id'    => $id,
                'count' => $result['response']['count'],
            ]
        ];

        return response()->json($response);
    }

    public function getAccountInfo($access_token)
    {
        return (new VkClient($access_token))->request('account.getProfileInfo');
    }

    public function setAccountData(Request $request)
    {
        $accountData = $this->getAccountInfo($request['access_token']);

        return Account::create([
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

        $response = (new VkClient($access_token))->request('newsfeed.get', [
            'filters'    => 'post',
            'count'      => 50,
            'start_from' => $request->input('start_from') ?? null
        ]);

        // Логирование ответа
        $this->loggingService->log(
            'account_newsfeed',
            $screen_name,
            'VK API Response',
            ['response' => $response]
        );

        return $response;
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
                    && $createdCount < $maxCreatedCount
                ) {
                    $attemptFailed = false;

                    $username = (new VkClient())->request('users.get', [
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

        return response($tasks);
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
                'token' => $access_token,
                'task'  => [
                    'owner_id' => $owner_id,
                    'item_id'  => $item_id,
                ],
            ]
        );

        $response = (new VkClient($access_token))->request('likes.add', [
            'type'     => 'post',
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ]);

        // Логирование ответа
        $this->loggingService->log(
            'account_like',
            $screen_name,
            'VK API Response',
            ['response' => $response]
        );

        return $response;
    }

    public function getScreenNameById(Request $request)
    {
        $user_id = $request->input('user_id');

        return (new VkClient())->request('users.get', [
            'fields'  => 'screen_name',
            'user_id' => $user_id
        ]);
    }

    private function getAccessTokenByAccountID($account_id)
    {
        return $account = DB::table('accounts')
                            ->where('account_id', $account_id)
                            ->value('access_token');
    }

    private function getScreenNameByToken($access_token)
    {
        return DB::table('accounts')
                 ->where('access_token', $access_token)
                 ->value('screen_name');
    }

}

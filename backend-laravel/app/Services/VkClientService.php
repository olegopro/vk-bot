<?php

namespace App\Services;

use App\Repositories\AccountRepositoryInterface;
use ATehnix\VkClient\Client;

class VkClientService
{
    private $api;
    private $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->api = new Client(config('services.vk.version'));
        $this->api->setDefaultToken(config('services.vk.token'));
        $this->accountRepository = $accountRepository;
    }

    public function request($method, $parameters = [], $token = null)
    {
        if ($token !== null) {
            $this->api->setDefaultToken($token);
        }

        return $this->api->request($method, $parameters);
    }

    public function allAccounts()
    {
        $data = $this->accountRepository->getAllAccounts();

        return [
            'success' => true,
            'data'    => $data,
            'message' => 'Список аккаунтов получен'
        ];
    }

    public function deleteAccount($id)
    {
        $this->accountRepository->deleteAccount($id);

        return [
            'success' => true,
            'message' => 'Аккаунт удален'
        ];
    }

    public function getAccountData($ids)
    {
        if (is_array($ids)) {
            $ids = implode(',', $ids);
        }

        $response = $this->request('users.get', [
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

        return [
            'success' => true,
            'data'    => $response,
            'message' => 'Информация о профиле получена'
        ];
    }

    public function getGroupData($id)
    {
        return $this->request('groups.getById', [
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

    public function getAccountFollowers($id, $limit)
    {
        return $this->request('users.getFollowers', [
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
        return $this->request('friends.get', [
            'user_id' => $id,
            'count'   => $limit,
            'fields'  => [
                'about',
                'photo_200'
            ]
        ]);
    }

    public function getAccountCountFriends($accountId, $ownerId, $token)
    {
        $ownerId = $ownerId === 'undefined' ? $accountId : $ownerId;

        $result = $this->request('friends.get', [
            'user_id' => $ownerId,
            'count'   => 1
        ], $token);

        return [
            'response' => [
                'id'    => $ownerId,
                'count' => $result['response']['count'],
            ]
        ];
    }

    public function getAccountInfo($token)
    {
        $response = $this->request('account.getProfileInfo', [], $token);

        return [
            'success' => true,
            'data'    => $response,
            'message' => 'Информация о профиле получена'
        ];
    }

    public function setAccountData($token, $accountRepository)
    {
        $accountInfoResponse = $this->getAccountInfo($token);

        if ($accountInfoResponse['success'] && isset($accountInfoResponse['data']['response'])) {
            $accountData = [
                'access_token' => $token,
                'account_id'   => $accountInfoResponse['data']['response']['id'],
                'screen_name'  => $accountInfoResponse['data']['response']['screen_name'],
                'first_name'   => $accountInfoResponse['data']['response']['first_name'],
                'last_name'    => $accountInfoResponse['data']['response']['last_name'],
                'bdate'        => $accountInfoResponse['data']['response']['bdate']
            ];

            $response = $accountRepository->createAccount($accountData);

            return [
                'success' => true,
                'data'    => $response,
                'message' => 'Аккаунт ' . $accountData['screen_name'] . ' добавлен'
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка получения данных аккаунта'
        ];
    }

    public function getAccountNewsfeed($accountId, $startFrom, $loggingService)
    {
        $access_token = $this->getAccessTokenByAccountID($accountId);
        $screen_name = $this->getScreenNameByToken($access_token);

        // Логирование запроса
        $loggingService->log(
            'account_newsfeed',
            $screen_name,
            'VK API Request',
            ['request' => ['account_id' => $accountId, 'start_from' => $startFrom]]
        );

        $response = $this->request('newsfeed.get', [
            'filters'    => 'post',
            'count'      => 40,
            'start_from' => $startFrom
        ], $access_token);

        // Логирование ответа
        $loggingService->log(
            'account_newsfeed',
            $screen_name,
            'VK API Response',
            ['response' => $response]
        );

        return [
            'success' => true,
            'data'    => $response,
            'message' => 'Получены новые данные ленты'
        ];
    }

    public function addLike($ownerId, $itemId, $accessToken, $loggingService)
    {
        $screenName = $this->getScreenNameByToken($accessToken);

        // Логирование запроса
        $loggingService->log(
            'account_like',
            $screenName,
            'VK API Request',
            [
                'request' => [
                    'token' => $accessToken,
                    'task'  => [
                        'owner_id' => $ownerId,
                        'item_id'  => $itemId,
                    ],
                ]
            ]
        );

        $response = $this->request('likes.add', [
            'type'     => 'post',
            'owner_id' => $ownerId,
            'item_id'  => $itemId
        ], $accessToken);

        // Логирование ответа
        $loggingService->log(
            'account_like',
            $screenName,
            'VK API Response',
            ['response' => $response]
        );

        return [
            'success' => true,
            'data'    => $response,
            'message' => 'Лайк успешно поставлен'
        ];
    }

    public function getAccessTokenByAccountID($account_id)
    {
        return $this->accountRepository->getAccessTokenByAccountID($account_id);
    }

    public function getScreenNameByToken($access_token)
    {
        return $this->accountRepository->getScreenNameByToken($access_token);
    }

    public function getLikes($access_token, $type, $owner_id, $item_id)
    {
        return $this->request('likes.getList', [
            'type'     => $type,
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ], $access_token);
    }

    public function getUsers($users_ids)
    {
        $response = $this->request('users.get', [
            'user_ids' => $users_ids,
        ]);

        return [
            'success' => true,
            'data'    => $response,
            'message' => 'Пользователи получены'
        ];
    }

    public function deleteLike($accessToken, $type, $owner_id, $item_id)
    {
        $response = $this->request('likes.delete', [
            'type'     => $type,
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ], $accessToken);

        if (isset($response['error'])) {
            return [
                'success' => false,
                'error'   => $response['error']['error_msg']
            ];
        }

        return [
            'success' => true,
            'message' => 'Лайк отменён'
        ];
    }
}

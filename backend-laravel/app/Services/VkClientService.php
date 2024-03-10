<?php

namespace App\Services;

use App\Repositories\AccountRepositoryInterface;
use ATehnix\VkClient\Client;
use ATehnix\VkClient\Exceptions\VkException;

/**
 * Класс VkClientService предоставляет сервисы для взаимодействия с VK API.
 *
 * Этот класс инкапсулирует логику отправки запросов к VK API, обработки полученных ответов,
 * а также управления ошибками, возникающими в процессе взаимодействия с API.
 */
class VkClientService
{
    /**
     * @var Client Инстанс клиента VK API.
     */
    private $api;

    /**
     * @var AccountRepositoryInterface Репозиторий аккаунтов для работы с данными аккаунтов.
     */
    private $accountRepository;

    /**
     * Конструктор класса VkClientService.
     *
     * @param AccountRepositoryInterface $accountRepository Репозиторий аккаунтов.
     */
    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->api = new Client(config('services.vk.version'));
        $this->api->setDefaultToken(config('services.vk.token'));
        $this->accountRepository = $accountRepository;
    }

    /**
     * Отправляет запрос к VK API.
     *
     * @param string $method Название метода VK API.
     * @param array $parameters Параметры запроса к API.
     * @param string|null $token Токен доступа. Если не указан, используется токен по умолчанию.
     * @return array Ответ от VK API.
     * @throws VkException
     */
    public function request($method, $parameters = [], $token = null)
    {
        if ($token !== null) {
            $this->api->setDefaultToken($token);
        }

        return $this->api->request($method, $parameters);
    }

    /**
     * Получает данные всех аккаунтов.
     *
     * @return array Данные аккаунтов.
     */
    public function fetchAllAccounts()
    {
        $data = $this->accountRepository->getAllAccounts();

        $pagination = collect($data->toArray())->except('data');

        return [
            'success' => true,
            'data'    => $data->items(), // Извлекаем только массив данных
            'pagination' => $pagination, // Добавляем информацию о пагинации, если нужно
            'message' => 'Список аккаунтов получен'
        ];

    }

    /**
     * Получает данные аккаунта по указанным ID.
     *
     * @param mixed $ids ID аккаунтов (массив или строка).
     * @return array Данные аккаунта(ов).
     * @throws VkException
     */
    public function fetchAccountData($ids)
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

    /**
     * Получает данные группы по её ID.
     *
     * @param int $id ID группы.
     * @return array Данные группы.
     * @throws VkException
     */
    public function fetchGroupData($id)
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

    /**
     * Получает подписчиков аккаунта.
     *
     * @param int $id ID аккаунта.
     * @param int $limit Ограничение на количество возвращаемых подписчиков.
     * @return array Подписчики аккаунта.
     * @throws VkException
     */
    public function fetchAccountFollowers($id, $limit)
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

    /**
     * Получает друзей аккаунта.
     *
     * @param int $id ID аккаунта.
     * @param int $limit Ограничение на количество возвращаемых друзей.
     * @return array Друзья аккаунта.
     * @throws VkException
     */
    public function fetchAccountFriends($id, $limit = 6)
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

    /**
     * Получает количество друзей аккаунта.
     *
     * @param int $accountId ID аккаунта.
     * @param int $ownerId ID владельца аккаунта.
     * @param string $token Токен доступа.
     * @return array[] Количество друзей.
     * @throws VkException
     */
    public function fetchAccountCountFriends($accountId, $ownerId, $token)
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

    /**
     * Получает информацию об аккаунте.
     *
     * @param string $token Токен доступа.
     * @return array Информация об аккаунте.
     * @throws VkException
     */
    public function fetchAccountInfo($token)
    {
        $response = $this->request('account.getProfileInfo', [], $token);

        return [
            'success' => true,
            'data'    => $response,
            'message' => 'Информация о профиле получена'
        ];
    }

    /**
     * Получает новостную ленту аккаунта.
     *
     * @param int $accountId ID аккаунта.
     * @param string $startFrom Стартовая точка для пагинации.
     * @param mixed $loggingService Сервис логирования.
     * @return array Новостная лента.
     * @throws VkException
     */
    public function fetchAccountNewsfeed($accountId, $startFrom, $loggingService)
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

    /**
     * Получает информацию о лайках к объекту.
     *
     * @param string $access_token Токен доступа.
     * @param string $type Тип объекта (например, post).
     * @param int $owner_id ID владельца объекта.
     * @param int $item_id ID объекта.
     * @return array Информация о лайках.
     * @throws VkException
     */
    public function fetchLikes($access_token, $type, $owner_id, $item_id)
    {
        return $this->request('likes.getList', [
            'type'     => $type,
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ], $access_token);
    }

    /**
     * Получает данные пользователей по их ID.
     *
     * @param array $users_ids Массив ID пользователей.
     * @return array Данные пользователей.
     * @throws VkException
     */
    public function fetchUsers($users_ids)
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

    /**
     * Получает токен доступа по ID аккаунта.
     *
     * @param int $account_id ID аккаунта.
     * @return string Токен доступа.
     */
    public function getAccessTokenByAccountID($account_id)
    {
        return $this->accountRepository->getAccessTokenByAccountID($account_id);
    }

    /**
     * Получает имя пользователя (screen name) по токену доступа.
     *
     * @param string $access_token Токен доступа.
     * @return string Имя пользователя.
     */
    public function getScreenNameByToken($access_token)
    {
        return $this->accountRepository->getScreenNameByToken($access_token);
    }

    /**
     * Устанавливает данные аккаунта.
     *
     * @param string $token Токен доступа.
     * @param AccountRepositoryInterface $accountRepository Репозиторий аккаунтов.
     * @return array
     * @throws VkException
     */
    public function setAccountData($token, $accountRepository)
    {
        $accountInfoResponse = $this->fetchAccountInfo($token);

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

    /**
     * Добавляет лайк к посту или другому объекту.
     *
     * @param int $ownerId ID владельца объекта.
     * @param int $itemId ID объекта.
     * @param string $accessToken Токен доступа для выполнения операции.
     * @param mixed $loggingService Сервис логирования.
     * @return array Результат добавления лайка.
     * @throws VkException
     */
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

    /**
     * Удаляет аккаунт по его ID.
     *
     * @param int $id ID аккаунта.
     * @return array Результат удаления аккаунта.
     */
    public function deleteAccount($id)
    {
        $this->accountRepository->deleteAccount($id);

        return [
            'success' => true,
            'message' => 'Аккаунт удален'
        ];
    }

    /**
     * Отменяет лайк с поста или другого объекта.
     *
     * @param string $accessToken Токен доступа.
     * @param string $type Тип объекта.
     * @param int $owner_id ID владельца объекта.
     * @param int $item_id ID объекта.
     * @return array Результат отмены лайка.
     * @throws VkException
     */
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

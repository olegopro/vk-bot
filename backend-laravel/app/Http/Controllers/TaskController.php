<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Filters\VkUserSearchFilter;
use App\Jobs\addLikeToPost;
use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TaskRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use ATehnix\VkClient\Exceptions\VkException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;
use OpenApi\Attributes as OA;
use Throwable;

/**
 * Контроллер для управления задачами, связанными с лайками в социальной сети ВКонтакте.
 */
final class TaskController extends Controller
{
    /**
     * Создает новый экземпляр TaskController.
     *
     * @param LoggingServiceInterface $loggingService Сервис логирования.
     * @param VkClientService $vkClient Сервис клиента ВКонтакте.
     * @param TaskRepositoryInterface $taskRepository Репозиторий задач.
     * @param AccountRepositoryInterface $accountRepository Репозиторий аккаунтов.
     * @param AccountController $accountController Контроллер аккаунтов.
     */
    public function __construct(
        private readonly LoggingServiceInterface    $loggingService,
        private readonly VkClientService            $vkClient,
        private readonly TaskRepositoryInterface    $taskRepository,
        private readonly AccountRepositoryInterface $accountRepository,
        private readonly AccountController          $accountController
    ) {}

    /**
     * Возвращает задачи или задачу по указанному статусу и/или ID аккаунта.
     *
     * @param string|null $status Статус задачи для фильтрации.
     * @param int|null $accountId ID аккаунта для фильтрации.
     * @return \Illuminate\Http\JsonResponse Ответ с данными о задачах.
     */
    #[OA\Get(
        path: '/tasks/{status?}/{accountId?}',
        description: 'Получает список задач с возможностью фильтрации по статусу и ID аккаунта. Возвращает статистику по статусам задач и полный список задач.',
        summary: 'Получить список задач',
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Статус задачи для фильтрации (queued, done, failed)',
        in: 'path',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            enum: ['queued', 'done', 'failed'],
            example: 'queued'
        )
    )]
    #[OA\Parameter(
        name: 'accountId',
        description: 'ID аккаунта для фильтрации задач',
        in: 'path',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 9121607
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешное получение списка задач',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'total', type: 'integer', example: 9),
                        new OA\Property(
                            property: 'statuses',
                            properties: [
                                new OA\Property(property: 'failed', type: 'integer', example: 0),
                                new OA\Property(property: 'queued', type: 'integer', example: 0),
                                new OA\Property(property: 'done', type: 'integer', example: 9)
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'tasks',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', example: 2),
                                            new OA\Property(property: 'job_id', type: 'integer', example: 2),
                                            new OA\Property(property: 'account_id', type: 'integer', example: 9121607),
                                            new OA\Property(property: 'owner_id', type: 'integer', example: 23391605),
                                            new OA\Property(property: 'first_name', type: 'string', example: 'Марина'),
                                            new OA\Property(property: 'last_name', type: 'string', example: 'Цепелина'),
                                            new OA\Property(property: 'item_id', type: 'integer', example: 7366),
                                            new OA\Property(property: 'error_message', type: 'string', example: null, nullable: true),
                                            new OA\Property(property: 'status', type: 'string', example: 'done'),
                                            new OA\Property(property: 'is_cyclic', type: 'integer', example: 0),
                                            new OA\Property(property: 'run_at', type: 'string', example: '2025-07-28 15:45:14'),
                                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-07-28T12:43:08.000000Z'),
                                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-07-28T12:45:16.000000Z')
                                        ],
                                        type: 'object'
                                    )
                                )
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'message', type: 'string', example: 'Список задач получен')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверные параметры запроса',
        content: new OA\JsonContent(
            ref: '#/components/schemas/TaskErrorResponse'
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(
            ref: '#/components/schemas/TaskErrorResponse'
        )
    )]
    public function getTasksByStatus(string|null $status = null, int|null $accountId = null): JsonResponse
    {
        // Проверяем, является ли первый параметр числом (accountId)
        if (is_numeric($status)) {
            $accountId = $status;
            $status    = null;
        }

        $tasks = $this->taskRepository->getTasksByStatus($status, $accountId, null);

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Список задач получен'
        ]);
    }

    /**
     * Получает информацию о задаче по её ID.
     *
     * @param int $taskId ID задачи.
     * @return \Illuminate\Http\JsonResponse Ответ с данными о задаче.
     * @throws VkException
     */
    #[OA\Get(
        path: '/task-info/{taskId}',
        description: 'Получает подробную информацию о задаче по её идентификатору, включая данные поста, лайки и пользователей',
        summary: 'Получить информацию о задаче',
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'taskId',
        description: 'Идентификатор задачи',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
            example: 123
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешное получение информации о задаче',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskInfoResponse')
    )]
    #[OA\Response(
        response: 404,
        description: 'Задача не найдена',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function getTaskInfo(int $taskId): JsonResponse
    {
        $taskData = $this->taskRepository->findTask($taskId);

        $ownerId   = $taskData->owner_id;
        $postId    = $taskData->item_id;
        $accountId = $taskData->account_id;

        $access_token = $this->accountRepository->getAccessTokenByAccountID($accountId);

        $postResponse = $this->vkClient->request('wall.getById', [
            'posts' => $ownerId . "_" . $postId,
        ], $access_token);

        $likesResponse = VkClient::fetchLikes($access_token, 'post', $ownerId, $postId);

        // Получение ID пользователей, которые поставили лайки
        $userIds = implode(',', $likesResponse['response']['items']);

        $usersResponse = VkClient::fetchUsers($userIds);

        // Извлекаем только нужные данные из поста
        $postData = $postResponse['response']['items'][0];

        $response = [
            'attachments' => $postData['attachments'] ?? [],
            'likes'       => $postData['likes'] ?? [],
            'liked_users' => $usersResponse['data']['response'], // Информация о пользователях
            'account_id'  => $accountId
        ];

        return response()->json([
            'success' => true,
            'data'    => $response,
            'message' => 'Данные о задаче получены'
        ]);
    }

    /**
     * Создает задачи на лайки из ленты новостей и добавляет их в очередь.
     * Дополнительно учитывает, является ли задача циклической, для корректной обработки внутри методов.
     *
     * @param Request $request HTTP-запрос.
     * @param bool $isCyclic Флаг, указывающий, является ли задача циклической.
     * @return \Illuminate\Http\JsonResponse Ответ с результатом добавления задач в очередь.
     * @throws VkException
     */
    #[OA\Post(
        path: '/tasks/get-posts-for-like',
        description: 'Создает и добавляет в очередь задачи на лайки из новостной ленты указанного аккаунта',
        summary: 'Создать задачи на лайки из новостной ленты',
        tags: ['Tasks']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['account_id', 'task_count'],
            properties: [
                new OA\Property(
                    property: 'account_id',
                    description: 'ID аккаунта для создания задач',
                    type: 'integer',
                    example: 9121607
                ),
                new OA\Property(
                    property: 'task_count',
                    description: 'Количество задач для создания',
                    type: 'integer',
                    example: 10
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Задачи успешно созданы',
        content: new OA\JsonContent(ref: '#/components/schemas/TasksListResponse')
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверные параметры запроса',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function createAndQueueLikeTasksFromNewsfeed(Request $request, bool $isCyclic = false): JsonResponse
    {
        $account_id   = $request->input('account_id');
        $task_count   = (int) $request->input('task_count');
        $access_token = VkClient::getAccessTokenByAccountID($account_id);

        $maxCreatedCount = $task_count;

        // Извлечение и подготовка постов
        $this->fetchPostsAndCreateLikeTasks($request, $account_id, $maxCreatedCount, $isCyclic);

        // После обработки всех постов, добавляем задачу на лайк в очередь
        return $this->processAndQueuePendingLikeTasks($access_token);
    }

    /**
     * Извлекает посты из ленты новостей и создает для них задачи на лайк.
     *
     * Этот метод выполняет несколько ключевых функций:
     * 1. Получает посты из ленты новостей пользователя.
     * 2. Проверяет каждый пост на соответствие критериям для создания задачи.
     * 3. Если пост подходит, проверяет, не была ли для этого поста уже создана задача.
     * 4. Если задача не была создана, создает новую задачу на лайк в статусе "pending".
     * 5. Процесс продолжается до тех пор, пока не будет достигнуто максимальное количество созданных задач
     *    или пока не закончатся посты в ленте.
     *
     * @param Request $request HTTP-запрос с параметрами для извлечения ленты новостей.
     * @param int $account_id ID аккаунта пользователя, для которого получаем ленту новостей.
     * @param int $maxCreatedCount Максимальное количество задач, которые необходимо создать.
     * @param bool $isCyclic Флаг, указывающий, является ли задача циклической.
     * @return int Количество созданных задач.
     * @throws VkException
     */
    protected function fetchPostsAndCreateLikeTasks(Request $request, int $account_id, int $maxCreatedCount, bool $isCyclic): int
    {
        $createdCount   = 0; // Счетчик созданных задач.
        $failedAttempts = 0; // Счетчик неудачных попыток получения подходящих постов.

        do {
            // Получаем посты из ленты новостей аккаунта.
            $result    = $this->accountController->fetchAccountNewsfeed($request)->getData(true);
            $data      = $result['data']['items']; // Посты из ленты новостей.
            $next_from = $result['data']['next_from']; // Курсор для следующего запроса.

            $attemptFailed = true; // Предполагаем, что попытка неудачна до обнаружения подходящего поста.

            // Перебираем посты и проверяем их на соответствие критериям для задачи на лайк.
            foreach ($data as $post) {
                if ($this->isValidPostForTask($post) && $createdCount < $maxCreatedCount) {
                    $attemptFailed = false; // Попытка успешна, найден подходящий пост.

                    // Проверяем, существует ли уже задача на лайк для этого поста.
                    $existingTask = $this->checkExistingTask($post['owner_id'], $post['post_id']);

                    if (!$existingTask) {
                        // Если задачи нет, создаем новую задачу на лайк.
                        $this->createPendingLikeTask($account_id, $post, $isCyclic);
                        $createdCount++; // Увеличиваем счетчик созданных задач.

                        // Если создано необходимое количество задач, прерываем цикл.
                        if ($createdCount >= $maxCreatedCount) {
                            // Если достигнуто максимальное количество созданных задач,
                            // 'break 2' прерывает выполнение как текущего цикла foreach,
                            // так и внешнего цикла do-while.
                            break 2;
                        }
                    }
                }
            }

            // Если после перебора всех постов не было успешных попыток, увеличиваем счетчик неудач.
            if ($attemptFailed) {
                $failedAttempts++;
            } else {
                $failedAttempts = 0; // Сбрасываем счетчик неудач, если была успешная попытка.
            }

            // Обновляем запрос для следующего набора постов.
            $request->merge(['start_from' => $next_from]);

            // Если количество неудачных попыток достигло 10, прерываем процесс.
            if ($failedAttempts >= 10) {
                break;
            }

            // Продолжаем пока не создадим необходимое количество задач или пока есть посты для обработки.
        } while ($createdCount < $maxCreatedCount && !empty($next_from));

        return $createdCount; // Возвращаем количество созданных задач.
    }

    /**
     * Проверяет, подходит ли пост для создания задачи на лайк.
     *
     * @param array $post Массив данных поста.
     * @return bool Возвращает true, если пост удовлетворяет всем условиям.
     */
    protected function isValidPostForTask(array $post): bool
    {
        return
            // Пост должен принадлежать пользователю (не группе).
            $post['owner_id'] > 0

            // Пост не должен быть репостом.
            && !array_key_exists('copy_history', $post)

            // Пользователь еще не ставил лайк этому посту.
            && $post['likes']['user_likes'] === 0

            // У поста должны быть вложения.
            && isset($post['attachments'])

            // Среди вложений должна быть фотография.
            && collect($post['attachments'])->contains('type', 'photo');
    }

    /**
     * Проверяет, существует ли уже задача для данного поста.
     *
     * @param int $ownerId ID владельца поста.
     * @param int $postId ID поста.
     * @return bool Возвращает true, если задача уже существует.
     */
    protected function checkExistingTask(int $ownerId, int $postId): bool
    {
        return Task::where('owner_id', $ownerId)
            ->where('item_id', $postId)
            ->first() !== null;
    }

    /**
     * Создает новую задачу на лайк в статусе "pending".
     *
     * Метод получает информацию о пользователе через VK API, создает запись в базе данных
     * с информацией о задаче и возвращает созданную задачу.
     *
     * @param int $accountId ID аккаунта, от имени которого будет выполняться задача.
     * @param array $post Данные поста, для которого создается задача на лайк.
     * @param bool $isCyclic Флаг, указывающий, является ли задача циклической.
     * @return Model|Task Созданная задача.
     * @throws VkException
     */
    protected function createPendingLikeTask(int $accountId, array $post, bool $isCyclic): Model|Task
    {
        $username = $this->vkClient->request('users.get', [
            'fields'  => 'screen_name',
            'user_id' => $post['owner_id']
        ]);

        // Предполагается, что запрос к API возвращает имя пользователя успешно
        $firstName = $username['response'][0]['first_name'] ?? 'Unknown';
        $lastName  = $username['response'][0]['last_name'] ?? 'Unknown';

        usleep(300000); // Пауза для имитации задержки между запросами к API

        return Task::create([
            'account_id' => $accountId,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'owner_id'   => $post['owner_id'],
            'item_id'    => $post['post_id'] ?? $post['id'],
            'status'     => 'pending',
            'is_cyclic'  => $isCyclic
        ]);
    }

    /**
     * Создает задачи на лайки для постов со стен пользователей и добавляет их в очередь.
     *
     * Метод принимает список доменов пользователей, получает первый пост со стены каждого пользователя,
     * создает для него задачу на лайк и добавляет все созданные задачи в очередь на выполнение.
     *
     * @param Request $request HTTP-запрос с параметрами.
     * @return \Illuminate\Http\JsonResponse Ответ с информацией о созданных задачах.
     * @throws VkException
     */
    #[OA\Post(
        path: '/tasks/create-like-tasks-for-user-wall-posts',
        description: 'Создает задачи на лайки для постов со стен указанных пользователей',
        summary: 'Создать задачи на лайки для постов пользователей',
        tags: ['Tasks']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['account_id', 'domains'],
            properties: [
                new OA\Property(
                    property: 'account_id',
                    description: 'ID аккаунта для создания задач',
                    type: 'integer',
                    example: 9121607
                ),
                new OA\Property(
                    property: 'domains',
                    description: 'Массив доменов пользователей',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['user1', 'user2', 'user3']
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Задачи успешно созданы',
        content: new OA\JsonContent(ref: '#/components/schemas/TasksListResponse')
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверные параметры запроса',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function createLikeTasksForUserWallPosts(Request $request): JsonResponse
    {
        $accountId    = $request->input('account_id');
        $domains      = $request->input('domains');
        $access_token = VkClient::getAccessTokenByAccountID($accountId);

        if (!$accountId || !$domains || !is_array($domains)) {
            return response()->json([
                'success' => false,
                'error'   => 'Неправильные параметры запроса'
            ], 400);
        }

        $tasks = [];

        Log::info('Domains data:', ['domains' => $domains]);

        foreach ($domains as $domain) {

            Log::info('Domain', ['domain' => $domain]);

            // Получаем записи со стены пользователя по его логину
            $wallPosts = $this->vkClient->fetchWallPostsByDomain($accountId, $domain, null, $this->loggingService);

            // Извлекаем первую запись из стены
            if (!empty($wallPosts['data']['response']['items'])) {
                $post = $wallPosts['data']['response']['items'][0];

                // Создаем задачу на лайк для извлеченной записи
                $task    = $this->createPendingLikeTask($accountId, $post, false);
                $tasks[] = $task;
            }
        }

        $this->processAndQueuePendingLikeTasks($access_token);

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Задачи на лайки созданы для пользователей'
        ]);
    }

    /**
     * Создает задачи на лайки для пользователей из указанного города.
     *
     * Метод ищет пользователей по городу через VK API с применением фильтров,
     * получает их посты и создает задачи на лайки.
     * Работает с пагинацией и завершается при 10 пустых ответах API подряд.
     *
     * ### Основные особенности:
     * - Ищет пользователей с открытыми профилями и screen_name
     * - Применяет дополнительные фильтры для более точного поиска
     * - Создает задачи только для постов с фотографиями
     * - Отправляет все созданные задачи в очередь
     * - Всегда возвращает success=true с информацией о результате
     *
     * ### Параметры:
     * - city_id: ID города для поиска пользователей
     * - account_id: ID аккаунта для выполнения задач
     * - count: желаемое количество задач (по умолчанию 10)
     * - sex: пол (1 - женщина, 2 - мужчина, null - любой)
     * - age_from: минимальный возраст
     * - age_to: максимальный возраст
     * - online_only: только онлайн пользователи
     * - last_seen_days: максимальное количество дней с последнего посещения
     * - is_friend: есть ли в друзьях у текущего пользователя
     *
     * @param Request $request HTTP-запрос с параметрами
     * @return \Illuminate\Http\JsonResponse Ответ с результатом
     */
    public function createLikeTasksForCityUsers(Request $request): JsonResponse
    {
        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'city_id'        => 'required|integer',
            'account_id'     => 'required|integer',
            'count'          => 'integer|min:1|max:1000|nullable',
            'sex'            => 'integer|in:1,2|nullable',
            'age_from'       => 'integer|min:14|max:80|nullable',
            'age_to'         => 'integer|min:14|max:80|nullable',
            'online_only'    => 'boolean|nullable',
            'has_photo'      => 'boolean|nullable',
            'sort'           => 'integer|in:0,1|nullable',
            'min_friends'    => 'integer|min:0|nullable',
            'max_friends'    => 'integer|min:0|nullable',
            'min_followers'  => 'integer|min:0|nullable',
            'max_followers'  => 'integer|min:0|nullable',
            'last_seen_days' => 'integer|min:1|nullable',
            'is_friend'      => 'boolean|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $accountId = (int) $request->input('account_id');
        $cityId    = (int) $request->input('city_id');
        $count     = (int) $request->input('count', 10);

        // Параметры фильтров
        $sex          = $request->input('sex');
        $ageFrom      = $request->input('age_from');
        $ageTo        = $request->input('age_to');
        $onlineOnly   = $request->boolean('online_only');
        $hasPhoto     = $request->input('has_photo');
        $sort         = $request->input('sort');
        $minFriends   = $request->input('min_friends');
        $maxFriends   = $request->input('max_friends');
        $minFollowers = $request->input('min_followers');
        $maxFollowers = $request->input('max_followers');
        $lastSeenDays = $request->input('last_seen_days');
        $isFriend     = $request->input('is_friend');

        try {
            $access_token      = VkClient::getAccessTokenByAccountID($accountId);
            $tasks             = [];
            $usedDomains       = [];
            $offset            = 0;
            $emptyResponses    = 0; // Счетчик пустых ответов от API
            $maxEmptyResponses = 1000; // Максимум 1000 пустых ответов подряд

            // Основной цикл: ищем пользователей и создаем задачи до достижения нужного количества
            while (count($tasks) < $count && $emptyResponses < $maxEmptyResponses) {
                $neededUsers = $count - count($tasks);
                $neededUsers = max($neededUsers, 10); // Минимум 10 пользователей за раз

                // Создаем фильтр с базовыми параметрами
                $filter = (new VkUserSearchFilter())
                    ->setCity($cityId)
                    ->setCount($neededUsers)
                    ->addFilter('offset', $offset);

                // Применяем фильтры пола и возраста
                if ($sex !== null) {
                    $filter->setSex($sex);
                }

                if ($ageFrom !== null) {
                    $filter->setAgeFrom($ageFrom);
                }

                if ($ageTo !== null) {
                    $filter->setAgeTo($ageTo);
                }

                if ($onlineOnly) {
                    $filter->setOnlineOnly(true);
                }
                
                if ($hasPhoto !== null) {
                    $filter->setHasPhoto($hasPhoto);
                }

                if ($sort !== null) {
                    $filter->setSort($sort);
                }

                if ($lastSeenDays !== null) {
                    $filter->setLastSeen($lastSeenDays);
                }

                if ($isFriend !== null) {
                    $filter->setIsFriend($isFriend);
                }

                // Применяем расширенные фильтры (через дополнительный API запрос)
                $filter->setExtendedFilters(
                    $minFriends,
                    $maxFriends,
                    $minFollowers,
                    $maxFollowers
                );

                $response = $this->vkClient->searchUsers($filter, $accountId);

                // Проверяем, пустой ли ответ
                if (empty($response['response']['items'])) {
                    $emptyResponses++; // Увеличиваем счетчик пустых ответов
                    $offset += $neededUsers; // Увеличиваем offset даже при пустом ответе
                    continue; // Переходим к следующей итерации
                }

                // Сбрасываем счетчик пустых ответов, так как нашли пользователей
                $emptyResponses = 0;

                foreach ($response['response']['items'] as $user) {
                    // Проверяем лимит задач
                    if (count($tasks) >= $count) {
                        break 2;
                    }

                    // Проверяем, что пользователь подходит и не использован
                    if (!empty($user['screen_name']) &&
                        isset($user['is_closed']) &&
                        $user['is_closed'] == 0 &&
                        !in_array($user['screen_name'], $usedDomains)) {

                        // Получаем посты пользователя
                        sleep(1); // Пауза между запросами
                        $wallPosts = $this->vkClient->fetchWallPostsByDomain($accountId, $user['screen_name'], null, $this->loggingService);

                        if (!empty($wallPosts['data']['response']['items'])) {
                            $post = $wallPosts['data']['response']['items'][0];

                            // Создаем задачу
                            $tasks[]       = $this->createPendingLikeTask($accountId, $post, false);
                            $usedDomains[] = $user['screen_name'];
                        }
                    }
                }

                $offset += $neededUsers;
            }

            // Отправляем задачи в очередь (ВСЕГДА, даже если меньше чем запрошено)
            $this->processAndQueuePendingLikeTasks($access_token);

            // Формируем ответ в зависимости от количества созданных задач
            $actualCount = count($tasks);
            $message     = $actualCount === $count
                ? 'Задачи на лайки созданы для пользователей из выбранного города'
                : "Создано $actualCount задач из $count (некоторые пользователи не имели подходящих постов)";

            return response()->json([
                'success'         => true,
                'data'            => $tasks,
                'message'         => $message,
                'requested_count' => $count,
                'actual_count'    => $actualCount,
                'found_users'     => count($usedDomains),
                'used_users'      => count($usedDomains),
                'applied_filters' => [
                    'city_id'        => $cityId,
                    'sex'            => $sex,
                    'age_from'       => $ageFrom,
                    'age_to'         => $ageTo,
                    'online_only'    => $onlineOnly,
                    'has_photo'      => $hasPhoto,
                    'sort'           => $sort,
                    'last_seen_days' => $lastSeenDays,
                    'is_friend'      => $isFriend
                ]
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обрабатывает задачи на лайки в статусе "pending" и добавляет их в очередь на выполнение.
     * Изменяет статус задач с "pending" на "queued" и создает задания в очереди Laravel.
     *
     * @param string $token Токен доступа для API ВКонтакте.
     * @return \Illuminate\Http\JsonResponse Ответ с информацией о задачах, добавленных в очередь.
     */
    #[OA\Post(
        path: '/tasks/add-task-likes',
        description: 'Обрабатывает задачи на лайки в статусе pending и добавляет их в очередь на выполнение',
        summary: 'Добавить задачи в очередь на выполнение',
        tags: ['Tasks']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['token'],
            properties: [
                new OA\Property(
                    property: 'token',
                    description: 'Токен доступа для API ВКонтакте',
                    type: 'string',
                    example: 'vk1.a.abc123def456'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Задачи успешно добавлены в очередь',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 123),
                            new OA\Property(property: 'status', type: 'string', example: 'pending'),
                            new OA\Property(property: 'account_id', type: 'integer', example: 9121607)
                        ]
                    )
                ),
                new OA\Property(property: 'message', type: 'string', example: 'Задачи успешно созданы и запланированы')
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверные параметры запроса',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function processAndQueuePendingLikeTasks(string $token): JsonResponse
    {
        // Базовое значение задержки из настроек
        $basePause = DB::table('settings')
            ->where('id', '=', '1')
            ->value('task_timeout');

        // Получаем все задачи со статусом 'pending'
        $tasks = Task::where('status', '=', 'pending')
            ->get();

        // Инициализируем переменную для хранения текущей задержки
        $pause = 0;

        foreach ($tasks as $task) {

            if ($task->is_cyclic) {
                // Для циклических задач устанавливаем фиксированную задержку в 1 минуту
                $specificPause = 60;
            } else {
                // Для нециклических задач используем задержку, увеличенную на значение из настроек
                $pause += $basePause;
                $specificPause = $pause;
            }

            $run_at = now()->addSeconds($specificPause);

            // Обновляем время запуска и статус задачи
            $task->update([
                'run_at' => $run_at,
                'status' => 'queued'
            ]);

            // Отправляем задачу в очередь с учетом задержки
            addLikeToPost::dispatch($task, $token, $this->loggingService)
                ->delay($specificPause);
        }

        // Возвращаем список оставшихся задач для информации
        $tasks = Task::where('status', '=', 'pending')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Задачи успешно созданы и запланированы'
        ]);
    }

    /**
     * Удаляет все задачи на основе указанного статуса и/или ID аккаунта.
     *
     * @param string|null $status Статус задач для удаления.
     * @param int|null $accountId ID аккаунта для удаления задач.
     * @return \Illuminate\Http\JsonResponse Ответ об успешном удалении задач.
     */
    #[OA\Delete(
        path: '/tasks/delete-all-tasks/{status?}/{accountId?}',
        description: 'Удаляет все задачи на основе указанного статуса и/или ID аккаунта. Если параметры не указаны, удаляются все задачи.',
        summary: 'Удалить все задачи',
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'status',
        description: 'Статус задач для удаления (queued, done, failed). Если не указан, удаляются задачи всех статусов',
        in: 'path',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            enum: ['queued', 'done', 'failed'],
            example: 'queued'
        )
    )]
    #[OA\Parameter(
        name: 'accountId',
        description: 'ID аккаунта для удаления задач. Если не указан, удаляются задачи всех аккаунтов',
        in: 'path',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            example: 9121607
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Задачи успешно удалены',
        content: new OA\JsonContent(ref: '#/components/schemas/DeleteSuccessResponse')
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверные параметры запроса',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function deleteAllTasks(string|null $status = null, int|null $accountId = null): JsonResponse
    {
        $this->taskRepository->clearQueueBasedOnStatus($status, $accountId);

        return response()->json([
            'success' => true,
            'message' => 'Задачи успешно удалены'
        ]);
    }

    /**
     * Удаляет задачу по её ID.
     *
     * @param int $id ID задачи для удаления.
     * @return \Illuminate\Http\JsonResponse Ответ об успешном удалении задачи.
     */
    #[OA\Delete(
        path: '/tasks/delete-task-by-id/{id}',
        description: 'Удаляет задачу по её идентификатору. Автоматически определяет статус задачи и выполняет соответствующее удаление.',
        summary: 'Удалить задачу по ID',
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Идентификатор задачи для удаления',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
            example: 12345
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Задача успешно удалена',
        content: new OA\JsonContent(ref: '#/components/schemas/DeleteSuccessResponse')
    )]
    #[OA\Response(
        response: 400,
        description: 'Неверные параметры запроса',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 404,
        description: 'Задача не найдена',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function deleteTaskById(int $id): JsonResponse
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

    /**
     * Удаляет лайк с поста, связанного с задачей по её ID.
     *
     * @param int $taskId ID задачи, для которой нужно удалить лайк.
     * @return \Illuminate\Http\JsonResponse Ответ об успешном удалении лайка или ошибке.
     * @throws VkException
     */
    #[OA\Delete(
        path: '/tasks/delete-like/{taskId}',
        description: 'Удаляет лайк с поста, связанного с указанной задачей',
        summary: 'Удалить лайк по ID задачи',
        tags: ['Tasks']
    )]
    #[OA\Parameter(
        name: 'taskId',
        description: 'ID задачи, для которой нужно удалить лайк',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
            example: 123
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Лайк успешно удален',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'message', type: 'string', example: 'Лайк успешно удален')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Задача не найдена',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/TaskErrorResponse')
    )]
    public function deleteLike(int $taskId): JsonResponse
    {
        $taskData = $this->taskRepository->findTask($taskId);

        if (!$taskData) {
            return response()->json([
                'success' => false,
                'error'   => 'Задача не найдена'
            ]);
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

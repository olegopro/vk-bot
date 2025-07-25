<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikeToPost;
use App\Models\CyclicTask;
use App\Models\Task;
use App\OpenApi\Schemas\TaskResponseSchema;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TaskRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use ATehnix\VkClient\Exceptions\VkException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;
use OpenApi\Attributes as OA;

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
        description: 'Получает список задач с возможностью фильтрации по статусу и ID аккаунта. Поддерживает пагинацию и возвращает статистику по статусам задач.',
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
    #[OA\Parameter(
        name: 'page',
        description: 'Номер страницы для пагинации',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1,
            example: 1
        )
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Количество элементов на странице',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 30,
            maximum: 100,
            minimum: 1,
            example: 30
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешное получение списка задач',
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
    public function getTasksByStatus(Request $request, $status = null, $accountId = null)
    {
        // Проверяем, является ли первый параметр числом (accountId)
        if (is_numeric($status)) {
            $accountId = $status;
            $status = null;
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
    public function getTaskInfo($taskId)
    {
        $taskData = $this->taskRepository->findTask($taskId);

        $ownerId = $taskData->owner_id;
        $postId = $taskData->item_id;
        $accountId = $taskData->account_id;

        $access_token = $this->accountRepository->getAccessTokenByAccountID($accountId);

        $postResponse = $this->vkClient->request('wall.getById', [
            'posts' => $ownerId . '_' . $postId,
        ], $access_token);

        $likesResponse = VkClient::fetchLikes($access_token, 'post', $ownerId, $postId);

        // Получение ID пользователей, которые поставили лайки
        $userIds = implode(',', $likesResponse['response']['items']);

        $usersResponse = VkClient::fetchUsers($userIds);

        // Извлекаем только нужные данные из поста
        $postData = $postResponse['response']['items'][0];

        $response = [
            'attachments' => $postData['attachments'] ?? [],
            'likes' => $postData['likes'] ?? [],
            'liked_users' => $usersResponse['data']['response'], // Информация о пользователях
            'account_id' => $accountId
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
    public function createAndQueueLikeTasksFromNewsfeed(Request $request, $isCyclic = false)
    {
        $account_id = $request->input('account_id');
        $task_count = $request->input('task_count');
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
    protected function fetchPostsAndCreateLikeTasks($request, $account_id, $maxCreatedCount, $isCyclic)
    {
        $createdCount = 0; // Счетчик созданных задач.
        $failedAttempts = 0; // Счетчик неудачных попыток получения подходящих постов.

        do {
            // Получаем посты из ленты новостей аккаунта.
            $result = $this->accountController->fetchAccountNewsfeed($request)->getData(true);
            $data = $result['data']['response']['items']; // Посты из ленты новостей.
            $next_from = $result['data']['response']['next_from']; // Курсор для следующего запроса.

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
                            // 'break 2;' прерывает выполнение как текущего цикла foreach,
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
    protected function isValidPostForTask($post)
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
    protected function checkExistingTask($ownerId, $postId)
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
     * @return Task Созданная задача.
     * @throws VkException
     */
    protected function createPendingLikeTask($accountId, $post, $isCyclic)
    {
        $username = $this->vkClient->request('users.get', [
            'fields'  => 'screen_name',
            'user_id' => $post['owner_id']
        ]);

        // Предполагается, что запрос к API возвращает имя пользователя успешно
        $firstName = $username['response'][0]['first_name'] ?? 'Unknown';
        $lastName = $username['response'][0]['last_name'] ?? 'Unknown';

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
    public function createLikeTasksForUserWallPosts(Request $request)
    {
        $accountId = $request->input('account_id');
        $domains = $request->input('domains');
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
                $task = $this->createPendingLikeTask($accountId, $post, false);
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
    public function processAndQueuePendingLikeTasks($token)
    {
        // Базовое значение задержки из настроек
        $basePause = DB::table('settings')
                       ->where('id', '=', '1')
                       ->value('task_timeout');

        // Получаем все задачи со статусом 'pending'
        $tasks = DB::table('tasks')
                   ->where('status', '=', 'pending')
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
            DB::table('tasks')
              ->where('id', $task->id)
              ->update([
                  'run_at' => $run_at,
                  'status' => 'queued'
              ]);

            // Отправляем задачу в очередь с учетом задержки
            addLikeToPost::dispatch($task, $token, $this->loggingService)
                         ->delay($specificPause);
        }

        // Возвращаем список оставшихся задач для информации
        $tasks = DB::table('tasks')
                   ->where('status', '=', 'pending')
                   ->get();

        return response()->json([
            'success' => true,
            'data'    => $tasks,
            'message' => 'Задачи успешно созданы и запланированы'
        ]);
    }

    /**
     * Создает циклическую задачу на лайки в социальной сети, используя предоставленные данные.
     *
     * Этот метод обрабатывает HTTP-запрос, содержащий необходимые данные для создания циклической задачи,
     * включая идентификатор аккаунта, количество задач в час, общее количество задач и статус задачи.
     * Он также генерирует уникальное расписание (массив уникальных случайных минут в течение часа),
     * в которое будут выполняться задачи, и сохраняет это расписание в базе данных.
     *
     * @param Request $request HTTP-запрос, содержащий следующие параметры:
     * - account_id: Идентификатор аккаунта, для которого создается задача.
     * - tasks_per_hour: Количество задач на лайки, которое должно быть выполнено в час.
     * - tasks_count: Общее количество задач на лайки, которое нужно выполнить.
     * - status: Статус задачи (например, 'active').
     *
     * @return \Illuminate\Http\JsonResponse Ответ, содержащий статус выполнения операции,
     * данные созданной циклической задачи и сообщение об успешном создании задачи.
     */
    #[OA\Post(
        path: '/tasks/create-cyclic-task',
        description: 'Создает циклическую задачу на лайки с автоматическим распределением времени выполнения',
        summary: 'Создать циклическую задачу на лайки',
        tags: ['Tasks']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['account_id', 'tasks_per_hour', 'total_task_count', 'status'],
            properties: [
                new OA\Property(
                    property: 'account_id',
                    description: 'ID аккаунта для создания задач',
                    type: 'integer',
                    example: 9121607
                ),
                new OA\Property(
                    property: 'tasks_per_hour',
                    description: 'Количество задач в час',
                    type: 'integer',
                    example: 10
                ),
                new OA\Property(
                    property: 'total_task_count',
                    description: 'Общее количество задач',
                    type: 'integer',
                    example: 100
                ),
                new OA\Property(
                    property: 'status',
                    description: 'Статус циклической задачи',
                    type: 'string',
                    example: 'active'
                ),
                new OA\Property(
                    property: 'selected_times',
                    description: 'Выбранные временные интервалы',
                    type: 'string',
                    example: '9-18'
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Циклическая задача успешно создана',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(
                    property: 'data',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'account_id', type: 'integer', example: 9121607),
                        new OA\Property(property: 'tasks_per_hour', type: 'integer', example: 10),
                        new OA\Property(property: 'total_task_count', type: 'integer', example: 100),
                        new OA\Property(property: 'remaining_tasks_count', type: 'integer', example: 100),
                        new OA\Property(property: 'status', type: 'string', example: 'active'),
                        new OA\Property(property: 'likes_distribution', type: 'string', example: '[5,15,25,35,45,55]'),
                        new OA\Property(property: 'selected_times', type: 'string', example: '9-18'),
                        new OA\Property(property: 'started_at', type: 'string', format: 'date-time')
                    ],
                    type: 'object'
                ),
                new OA\Property(property: 'message', type: 'string', example: 'Задача на постановку лайков запланирована.')
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
    public function createCyclicTask(Request $request)
    {
        // Генерация массива уникальных случайных минут
        $tasksPerHour = $request->input('tasks_per_hour');
        $uniqueMinutes = $this->generateUniqueRandomMinutes($tasksPerHour);

        // Создание циклической задачи с заполнением поля likes_distribution
        $cyclicTask = CyclicTask::create([
            'account_id'            => $request->input('account_id'),
            'tasks_per_hour'        => $tasksPerHour,
            'total_task_count'      => $request->input('total_task_count'),
            'remaining_tasks_count' => $request->input('total_task_count'),
            'status'                => $request->input('status'),
            'likes_distribution'    => json_encode($uniqueMinutes), // Сохраняем как строку
            'selected_times'        => $request->input('selected_times'),
            'started_at'            => now()
        ]);

        return response()->json([
            'success' => true,
            'data'    => $cyclicTask,
            'message' => 'Задача на постановку лайков запланирована.'
        ]);
    }

    /**
     * Подсчитывает количество задач по указанному статусу и/или ID аккаунта.
     *
     * @param string|null $status Статус задачи для фильтрации.
     * @param int|null $accountId ID аккаунта для фильтрации.
     * @return \Illuminate\Http\JsonResponse Ответ с количеством задач.
     */
    #[OA\Get(
        path: '/tasks/count/{status?}/{accountId?}',
        description: 'Подсчитывает количество задач по указанному статусу и/или ID аккаунта',
        summary: 'Подсчитать количество задач',
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
        description: 'Количество задач успешно получено',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'success', type: 'boolean', example: true),
                new OA\Property(property: 'data', type: 'integer', example: 42),
                new OA\Property(property: 'message', type: 'string', example: 'Количество задач получено')
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
    public function countTasksByAccountAndStatus($status = null, $accountId = null)
    {
        $count = $this->taskRepository->countTasksByAccountAndStatus($status, $accountId);

        return response()->json([
            'success' => true,
            'data'    => $count,
            'message' => 'Количество задач получено'
        ]);
    }

    /**
     * Генерирует массив уникальных случайных минут для выполнения задач в течение одного часа.
     *
     * Этот метод используется для создания расписания выполнения задач на лайки в социальной сети,
     * гарантируя, что каждая задача будет запланирована на уникальную минуту в пределах одного часа.
     * Таким образом обеспечивается равномерное распределение задач во времени.
     *
     * @param int $count Количество уникальных минут (задач), которое необходимо сгенерировать.
     *                  Это значение должно быть меньше или равно 60, так как в часе 60 минут.
     *
     * @return array Массив, содержащий уникальные случайные минуты в диапазоне от 1 до 60.
     *               Каждое значение в массиве указывает минуту в часе, когда должна быть выполнена задача.
     */
    public function generateUniqueRandomMinutes(int $count): array
    {
        $minutes = [];

        while (count($minutes) < $count) {
            $randomMinute = rand(1, 60);
            if (!in_array($randomMinute, $minutes)) {
                $minutes[] = $randomMinute;
            }
        }

        // Сортируем массив минут по возрастанию
        sort($minutes);

        return $minutes;
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
    public function deleteAllTasks($status = null, $accountId = null)
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
    public function deleteLike($taskId)
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

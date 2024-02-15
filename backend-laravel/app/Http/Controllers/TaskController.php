<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikeToPost;
use App\Models\CyclicTask;
use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Repositories\TaskRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Контроллер для управления задачами связанными с лайками в социальной сети ВКонтакте.
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
     * Возвращает статус задачи или задач по указанному статусу и/или ID аккаунта.
     *
     * @param string|null $status Статус задачи для фильтрации.
     * @param int|null $accountId ID аккаунта для фильтрации.
     * @return \Illuminate\Http\JsonResponse Ответ с данными о задачах.
     */
    public function getTaskStatus($status = null, $accountId = null)
    {
        // Проверяем, является ли первый параметр числом (accountId)
        if (is_numeric($status)) {
            $accountId = $status;
            $status = null;
        }

        $tasks = $this->taskRepository->getTaskStatus($status, $accountId);

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
     */
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

        // Деструктуризация 'response' для получения первого элемента
        list($response) = $postResponse['response'];

        $response['liked_users'] = $usersResponse['data']['response']; // Информация о пользователях
        $response['account_id'] = $accountId;

        return response()->json([
            'success' => true,
            'data'    => $response,
            'message' => 'Данные о задаче получены'
        ]);
    }

    /**
     * Собирает посты из ленты новостей для создания задачи на лайк.
     *
     * @param Request $request HTTP-запрос.
     * @return \Illuminate\Http\JsonResponse Ответ с результатом добавления задач в очередь.
     */
    public function collectNewsfeedPostsForLikeTask(Request $request)
    {
        $account_id = $request->input('account_id');
        $task_count = $request->input('task_count');
        $access_token = VkClient::getAccessTokenByAccountID($account_id);

        $maxCreatedCount = $task_count;

        // Извлечение и подготовка постов
        $createdCount = $this->fetchAndPreparePosts($request, $account_id, $maxCreatedCount);

        // После обработки всех постов, добавляем задачу на лайк в очередь
        return $this->addLikeTaskToQueue($access_token);
    }

    /**
     * Извлекает посты из ленты новостей и подготавливает их для создания задач на лайк.
     *
     * Этот метод выполняет несколько ключевых функций:
     * 1. Получает посты из ленты новостей пользователя.
     * 2. Проверяет каждый пост на соответствие критериям для создания задачи.
     * 3. Если пост подходит, проверяет, не была ли для этого поста уже создана задача.
     * 4. Если задача не была создана, создает новую задачу на лайк.
     * 5. Процесс продолжается до тех пор, пока не будет достигнуто максимальное количество созданных задач
     *    или пока не закончатся посты в ленте.
     *
     * @param Request $request HTTP-запрос с параметрами для извлечения ленты новостей.
     * @param int $account_id ID аккаунта пользователя, для которого получаем ленту новостей.
     * @param int $maxCreatedCount Максимальное количество задач, которые необходимо создать.
     * @return int Количество созданных задач.
     */
    protected function fetchAndPreparePosts($request, $account_id, $maxCreatedCount)
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
                        $this->createLikeTask($account_id, $post);
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
     * Создает задачу на лайк для указанного поста.
     *
     * @param int $accountId ID аккаунта пользователя.
     * @param array $post Массив данных поста.
     * @return Task Созданная задача.
     */
    protected function createLikeTask($accountId, $post)
    {
        $username = $this->vkClient->request('users.get', [
            'fields'  => 'screen_name',
            'user_id' => $post['owner_id']
        ]);

        // Предполагается, что запрос к API возвращает имя пользователя успешно
        $firstName = $username['response'][0]['first_name'] ?? 'Unknown';
        $lastName = $username['response'][0]['last_name'] ?? 'Unknown';

        usleep(300000); // Задержка для имитации задержки между запросами к API

        return Task::create([
            'account_id'    => $accountId,
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'owner_id'      => $post['owner_id'],
            'item_id'       => $post['post_id'],
            'attempt_count' => 1,
            'status'        => 'pending'
        ]);
    }

    /**
     * Добавляет задачу на лайк в очередь.
     *
     * @param string $token Токен доступа для API ВКонтакте.
     * @return \Illuminate\Http\JsonResponse Ответ с информацией о задачах, добавленных в очередь.
     */
    public function addLikeTaskToQueue($token)
    {
        $increase = $pause = DB::table('settings')
                               ->where('id', '=', '1')
                               ->value('task_timeout');

        $tasks = DB::table('tasks')
                   ->where('status', '=', 'pending')
                   ->get();

        foreach ($tasks as $task) {
            $run_at = now()->addSeconds($pause);

            // Обновляем время запуска и статус задачи
            DB::table('tasks')
              ->where('id', $task->id)
              ->update([
                  'run_at' => $run_at,
                  'status' => 'queued' // Обновляем статус на 'queued'
              ]);

            // Затем отправляем задачу в очередь
            addLikeToPost::dispatch($task, $token, $this->loggingService)
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

    /**
     * Создает циклическую задачу на лайки.
     *
     * @param Request $request HTTP-запрос, содержащий данные для создания циклической задачи.
     * @return \Illuminate\Http\JsonResponse Ответ с информацией о созданной циклической задаче.
     */
    public function createCyclicTask(Request $request)
    {
        $cyclicTask = CyclicTask::create([
            'account_id'     => $request->input('account_id'),
            'tasks_per_hour' => $request->input('tasks_per_hour'),
            'tasks_count'    => $request->input('tasks_count'),
            'status'         => $request->input('status'),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $cyclicTask,
            'message' => 'Задача на постановку лайков запланирована.'
        ]);

    }

    /**
     * Удаляет все задачи на основе указанного статуса и/или ID аккаунта.
     *
     * @param string|null $status Статус задач для удаления.
     * @param int|null $accountId ID аккаунта для удаления задач.
     * @return \Illuminate\Http\JsonResponse Ответ об успешном удалении задач.
     */
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
     */
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

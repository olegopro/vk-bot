<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\CyclicTaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use App\Models\CyclicTask;

final class CyclicTaskController extends Controller
{
    public function __construct(
        private readonly CyclicTaskRepositoryInterface $cyclicTaskRepository
    ) {}

    /**
     * Получить список циклических задач с пагинацией
     *
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/cyclic-tasks',
        description: 'Возвращает список всех циклических задач',
        summary: 'Получить список циклических задач',
        tags: ['Cyclic Tasks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список циклических задач успешно получен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'account_id', type: 'integer', example: 123),
                                    new OA\Property(property: 'total_task_count', type: 'integer', example: 100),
                                    new OA\Property(property: 'remaining_tasks_count', type: 'integer', example: 75),
                                    new OA\Property(property: 'tasks_per_hour', type: 'integer', example: 5),
                                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'pause', 'done'], example: 'active'),
                                    new OA\Property(property: 'selected_times', type: 'object', example: '{"monday": [true, false, true]}'),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time')
                                ]
                            )
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Список циклических задач получен')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Произошла ошибка при получении списка задач')
                    ]
                )
            )
        ]
    )]
    public function getCyclicTasks(): JsonResponse
    {
        // Получаем все циклические задачи без пагинации
        $cyclicTasks = $this->cyclicTaskRepository->getAllCyclicTasks();

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasks,
            'message' => 'Список циклических задач получен'
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
        path: '/cyclic-tasks/create-cyclic-task',
        description: 'Создает циклическую задачу на лайки с автоматическим распределением времени выполнения',
        summary: 'Создать циклическую задачу на лайки',
        tags: ['Cyclic Tasks']
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
                    description: 'Выбранные часы для каждого дня недели',
                    type: 'object',
                    example: '{"пн": [true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false], "вт": [false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true]}'
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
                        new OA\Property(property: 'selected_times', type: 'object', example: '{"пн": [true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false], "вт": [false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true, false, true]}'),
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
    public function createCyclicTask(Request $request): JsonResponse
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
     * Редактировать циклическую задачу
     *
     * @param Request $request HTTP запрос с данными для обновления
     * @param int $taskId ID циклической задачи
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/cyclic-tasks/{taskId}',
        description: 'Обновляет данные существующей циклической задачи',
        summary: 'Редактировать циклическую задачу',
        requestBody: new OA\RequestBody(
            description: 'Данные для обновления циклической задачи',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'account_id', description: 'ID аккаунта', type: 'integer', example: 123),
                    new OA\Property(property: 'total_task_count', description: 'Общее количество задач', type: 'integer', example: 100),
                    new OA\Property(property: 'tasks_per_hour', description: 'Количество задач в час', type: 'integer', example: 5),
                    new OA\Property(property: 'status', description: 'Статус задачи', type: 'string', enum: ['active', 'pause', 'done'], example: 'active'),
                    new OA\Property(property: 'selected_times', description: 'Выбранные времена выполнения', type: 'object', example: '{"monday": [true, false, true]}')
                ]
            )
        ),
        tags: ['Cyclic Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'taskId',
                description: 'ID циклической задачи для редактирования',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Циклическая задача успешно обновлена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'account_id', type: 'integer', example: 123),
                                new OA\Property(property: 'total_task_count', type: 'integer', example: 100),
                                new OA\Property(property: 'remaining_tasks_count', type: 'integer', example: 75),
                                new OA\Property(property: 'tasks_per_hour', type: 'integer', example: 5),
                                new OA\Property(property: 'status', type: 'string', example: 'active'),
                                new OA\Property(property: 'selected_times', type: 'object'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time')
                            ]
                        ),
                        new OA\Property(property: 'message', type: 'string', example: 'Циклическая задача обновлена')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Циклическая задача не найдена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Циклическая задача не найдена')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Произошла ошибка при обновлении задачи')
                    ]
                )
            )
        ]
    )]
    public function editCyclicTask(Request $request, int $taskId): JsonResponse
    {
        $data = $request->only(['account_id', 'total_task_count', 'tasks_per_hour', 'status', 'selected_times']);
        $cyclicTasks = $this->cyclicTaskRepository->editCyclicTask($taskId, $data);

        if (!$cyclicTasks) {
            return response()->json([
                'success' => false,
                'message' => 'Циклическая задача не найдена',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasks,
            'message' => 'Циклическая задача обновлена',
        ]);

    }

    /**
     * Удалить циклическую задачу
     *
     * @param int $taskId ID циклической задачи
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/cyclic-tasks/{taskId}',
        description: 'Удаляет циклическую задачу по её ID',
        summary: 'Удалить циклическую задачу',
        tags: ['Cyclic Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'taskId',
                description: 'ID циклической задачи для удаления',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Циклическая задача успешно удалена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Циклическая задача удалена')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Циклическая задача не найдена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Циклическая задача не найдена')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Произошла ошибка при удалении задачи')
                    ]
                )
            )
        ]
    )]
    public function deleteCyclicTask(int $taskId): JsonResponse
    {
        $cyclicTask = $this->cyclicTaskRepository->deleteCyclicTask($taskId);

        if (!$cyclicTask) {
            return response()->json([
                'success' => false,
                'error'   => 'Циклическая задача не найдена'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Циклическая задача удалена'
        ]);
    }

    /**
     * Удалить все циклические задачи
     *
     * @return JsonResponse
     */
    #[OA\Delete(
        path: '/cyclic-tasks/delete-all-cyclic-tasks',
        description: 'Удаляет все циклические задачи из системы',
        summary: 'Удалить все циклические задачи',
        tags: ['Cyclic Tasks'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Все циклические задачи успешно удалены',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Все циклические задачи удалены')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Произошла ошибка при удалении всех задач')
                    ]
                )
            )
        ]
    )]
    public function deleteAllCyclicTasks(): JsonResponse
    {
        $this->cyclicTaskRepository->deleteAllCyclicTasks();

        return response()->json([
            'success' => true,
            'message' => 'Все циклические задачи удалены'
        ]);
    }

    /**
     * Приостановить/возобновить циклическую задачу
     *
     * @param int $taskId ID циклической задачи
     * @return JsonResponse
     */
    #[OA\Patch(
        path: '/cyclic-tasks/pause-cyclic-task/{taskId}',
        description: 'Переключает статус циклической задачи между активным и приостановленным',
        summary: 'Приостановить/возобновить циклическую задачу',
        tags: ['Cyclic Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'taskId',
                description: 'ID циклической задачи для изменения статуса',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Статус циклической задачи успешно изменен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Циклическая задача поставлена на паузу')
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Циклическая задача не найдена',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'error', type: 'string', example: 'Циклическая задача не найдена')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Произошла ошибка при изменении статуса задачи')
                    ]
                )
            )
        ]
    )]
    public function pauseCyclicTask(int $taskId): JsonResponse
    {
        $result = $this->cyclicTaskRepository->pauseCyclicTask($taskId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'error'   => 'Циклическая задача не найдена'
            ]);
        }

        $message = $result['statusChangedTo'] === 'pause' ?
            'Циклическая задача поставлена на паузу' :
            'Циклическая задача возобновлена';

        return response()->json([
            'success' => true,
            'message' => $message
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
}

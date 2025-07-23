<?php

namespace App\Http\Controllers;

use App\Repositories\CyclicTaskRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

final class CyclicTaskController extends Controller
{
    public function __construct(
        private readonly CyclicTaskRepositoryInterface $cyclicTaskRepository
    ) {}

    /**
     * Получить список циклических задач с пагинацией
     *
     * @param Request $request HTTP запрос с параметрами пагинации
     * @return JsonResponse
     */
    #[OA\Get(
        path: '/api/cyclic-tasks',
        description: 'Возвращает пагинированный список всех циклических задач',
        summary: 'Получить список циклических задач',
        tags: ['Cyclic Tasks'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: 'Номер страницы для пагинации',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'perPage',
                description: 'Количество задач на странице',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, example: 30)
            )
        ],
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
                        new OA\Property(
                            property: 'pagination',
                            properties: [
                                new OA\Property(property: 'total', type: 'integer', example: 150),
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 5),
                                new OA\Property(property: 'per_page', type: 'integer', example: 30)
                            ]
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
    public function getCyclicTasks(Request $request): JsonResponse
    {
        // Получаем экземпляр пагинатора
        $cyclicTasksPaginator = $this->cyclicTaskRepository->getCyclicTasks($request->query('perPage', 30));

        // Извлекаем данные пагинации
        $pagination = collect($cyclicTasksPaginator->toArray())->except('data');

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasksPaginator->items(), // Извлекаем только массив данных
            'pagination' => $pagination, // Добавляем информацию о пагинации, если нужно
            'message' => 'Список циклических задач получен'
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
        path: '/api/cyclic-tasks/{taskId}',
        description: 'Обновляет данные существующей циклической задачи',
        summary: 'Редактировать циклическую задачу',
        requestBody: new OA\RequestBody(
            description: 'Данные для обновления циклической задачи',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'account_id', type: 'integer', description: 'ID аккаунта', example: 123),
                    new OA\Property(property: 'total_task_count', type: 'integer', description: 'Общее количество задач', example: 100),
                    new OA\Property(property: 'tasks_per_hour', type: 'integer', description: 'Количество задач в час', example: 5),
                    new OA\Property(property: 'status', type: 'string', enum: ['active', 'pause', 'done'], description: 'Статус задачи', example: 'active'),
                    new OA\Property(property: 'selected_times', type: 'object', description: 'Выбранные времена выполнения', example: '{"monday": [true, false, true]}')
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
    public function editCyclicTask(Request $request, $taskId): JsonResponse
    {
        $data = $request->only(['account_id','total_task_count', 'tasks_per_hour', 'status', 'selected_times']);
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
        path: '/api/cyclic-tasks/{taskId}',
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
    public function deleteCyclicTask($taskId): JsonResponse
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
        path: '/api/cyclic-tasks/delete-all-cyclic-tasks',
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
        path: '/api/cyclic-tasks/pause-cyclic-task/{taskId}',
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
    public function pauseCyclicTask($taskId): JsonResponse
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
}

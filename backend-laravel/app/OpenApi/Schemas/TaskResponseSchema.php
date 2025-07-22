<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: self::TASK_INFO_RESPONSE_SCHEMA,
    description: "Ответ с информацией о задаче",
    properties: [
        new OA\Property(
            property: "success",
            description: "Статус успешности запроса",
            type: "boolean",
            example: true
        ),
        new OA\Property(
            property: "data",
            description: "Данные о задаче",
            properties: [
                new OA\Property(
                    property: "attachments",
                    description: "Вложения поста",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: "type",
                                description: "Тип вложения",
                                type: "string",
                                example: "photo"
                            ),
                            new OA\Property(
                                property: "photo",
                                description: "Данные фотографии",
                                properties: [
                                    new OA\Property(
                                        property: "id",
                                        description: "ID фотографии",
                                        type: "integer",
                                        example: 457277432
                                    ),
                                    new OA\Property(
                                        property: "owner_id",
                                        description: "ID владельца фотографии",
                                        type: "integer",
                                        example: 357817305
                                    ),
                                    new OA\Property(
                                        property: "sizes",
                                        description: "Размеры фотографии",
                                        type: "array",
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(
                                                    property: "type",
                                                    description: "Тип размера",
                                                    type: "string",
                                                    example: "x"
                                                ),
                                                new OA\Property(
                                                    property: "width",
                                                    description: "Ширина",
                                                    type: "integer",
                                                    example: 640
                                                ),
                                                new OA\Property(
                                                    property: "height",
                                                    description: "Высота",
                                                    type: "integer",
                                                    example: 395
                                                ),
                                                new OA\Property(
                                                    property: "url",
                                                    description: "URL изображения",
                                                    type: "string",
                                                    example: "https://sun9-41.userapi.com/s/v1/if2/..."
                                                )
                                            ],
                                            type: "object"
                                        )
                                    )
                                ],
                                type: "object"
                            )
                        ],
                        type: "object"
                    )
                ),
                new OA\Property(
                    property: "likes",
                    description: "Информация о лайках",
                    properties: [
                        new OA\Property(
                            property: "count",
                            description: "Количество лайков",
                            type: "integer",
                            example: 9
                        ),
                        new OA\Property(
                            property: "user_likes",
                            description: "Поставил ли текущий пользователь лайк",
                            type: "integer",
                            example: 1
                        ),
                        new OA\Property(
                            property: "can_like",
                            description: "Может ли пользователь поставить лайк",
                            type: "integer",
                            example: 0
                        )
                    ],
                    type: "object"
                ),
                new OA\Property(
                    property: "liked_users",
                    description: "Пользователи, поставившие лайки",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: "id",
                                description: "ID пользователя",
                                type: "integer",
                                example: 713861431
                            ),
                            new OA\Property(
                                property: "first_name",
                                description: "Имя пользователя",
                                type: "string",
                                example: "Андрей"
                            ),
                            new OA\Property(
                                property: "last_name",
                                description: "Фамилия пользователя",
                                type: "string",
                                example: "Терещенко"
                            )
                        ],
                        type: "object"
                    )
                ),
                new OA\Property(
                    property: "account_id",
                    description: "ID аккаунта из модели Task",
                    type: "integer",
                    example: 9121607
                )
            ],
            type: "object"
        ),
        new OA\Property(
            property: "message",
            description: "Сообщение о результате операции",
            type: "string",
            example: "Данные о задаче получены"
        )
    ],
    type: "object"
)]

#[OA\Schema(
    schema: self::ERROR_RESPONSE_SCHEMA,
    description: "Ответ с ошибкой",
    properties: [
        new OA\Property(
            property: "success",
            description: "Статус успешности запроса",
            type: "boolean",
            example: false
        ),
        new OA\Property(
            property: "message",
            description: "Сообщение об ошибке",
            type: "string",
            example: "Произошла ошибка при получении данных о задаче"
        )
    ],
    type: "object"
)]

#[OA\Schema(
    schema: self::TASK_SCHEMA,
    description: "Модель задачи",
    properties: [
        new OA\Property(
            property: "id",
            description: "ID задачи",
            type: "integer",
            example: 123
        ),
        new OA\Property(
            property: "job_id",
            description: "ID задания в очереди",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "account_id",
            description: "ID аккаунта",
            type: "integer",
            example: 9121607
        ),
        new OA\Property(
            property: "first_name",
            description: "Имя пользователя",
            type: "string",
            example: "Андрей"
        ),
        new OA\Property(
            property: "last_name",
            description: "Фамилия пользователя",
            type: "string",
            example: "Терещенко"
        ),
        new OA\Property(
            property: "owner_id",
            description: "ID владельца поста",
            type: "integer",
            example: 357817305
        ),
        new OA\Property(
            property: "item_id",
            description: "ID поста",
            type: "integer",
            example: 457277432
        ),
        new OA\Property(
            property: "status",
            description: "Статус задачи",
            type: "string",
            enum: ["queued", "done", "failed"],
            example: "queued"
        ),
        new OA\Property(
            property: "is_cyclic",
            description: "Является ли задача циклической",
            type: "integer",
            example: 0
        ),
        new OA\Property(
            property: "error_message",
            description: "Сообщение об ошибке (если есть)",
            type: "string",
            nullable: true,
            example: null
        ),
        new OA\Property(
            property: "run_at",
            description: "Время запуска задачи",
            type: "string",
            example: "2025-07-19 18:31:08"
        ),
        new OA\Property(
            property: "created_at",
            description: "Время создания задачи",
            type: "string",
            format: "date-time",
            example: "2024-01-15T10:00:00Z"
        ),
        new OA\Property(
            property: "updated_at",
            description: "Время последнего обновления задачи",
            type: "string",
            format: "date-time",
            example: "2024-01-15T10:30:00Z"
        )
    ],
    type: "object"
)]

#[OA\Schema(
    schema: self::PAGINATION_SCHEMA,
    description: "Модель пагинации",
    properties: [
        new OA\Property(
            property: "current_page",
            description: "Текущая страница",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "data",
            description: "Данные на текущей странице",
            type: "array",
            items: new OA\Items(ref: self::TASK_REF)
        ),
        new OA\Property(
            property: "first_page_url",
            description: "URL первой страницы",
            type: "string",
            example: "http://localhost/api/tasks?page=1"
        ),
        new OA\Property(
            property: "from",
            description: "Номер первого элемента на странице",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "last_page",
            description: "Номер последней страницы",
            type: "integer",
            example: 5
        ),
        new OA\Property(
            property: "last_page_url",
            description: "URL последней страницы",
            type: "string",
            example: "http://localhost/api/tasks?page=5"
        ),
        new OA\Property(
            property: "next_page_url",
            description: "URL следующей страницы",
            type: "string",
            nullable: true,
            example: "http://localhost/api/tasks?page=2"
        ),
        new OA\Property(
            property: "path",
            description: "Базовый путь",
            type: "string",
            example: "http://localhost/api/tasks"
        ),
        new OA\Property(
            property: "per_page",
            description: "Количество элементов на странице",
            type: "integer",
            example: 30
        ),
        new OA\Property(
            property: "prev_page_url",
            description: "URL предыдущей страницы",
            type: "string",
            nullable: true,
            example: null
        ),
        new OA\Property(
            property: "to",
            description: "Номер последнего элемента на странице",
            type: "integer",
            example: 30
        ),
        new OA\Property(
            property: "total",
            description: "Общее количество элементов",
            type: "integer",
            example: 150
        ),
        new OA\Property(
            property: "links",
            description: "Ссылки для навигации по страницам",
            type: "array",
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: "url",
                        description: "URL ссылки",
                        type: "string",
                        nullable: true,
                        example: "http://localhost:8080/api/tasks?page=1"
                    ),
                    new OA\Property(
                        property: "label",
                        description: "Текст ссылки",
                        type: "string",
                        example: "1"
                    ),
                    new OA\Property(
                        property: "active",
                        description: "Активна ли ссылка",
                        type: "boolean",
                        example: true
                    )
                ],
                type: "object"
            )
        )
    ],
    type: "object"
)]

#[OA\Schema(
    schema: self::TASKS_LIST_RESPONSE_SCHEMA,
    description: "Ответ со списком задач",
    properties: [
        new OA\Property(
            property: "success",
            description: "Статус успешности запроса",
            type: "boolean",
            example: true
        ),
        new OA\Property(
            property: "data",
            description: "Данные ответа",
            properties: [
                new OA\Property(
                    property: "total",
                    description: "Общее количество задач",
                    type: "integer",
                    example: 150
                ),
                new OA\Property(
                    property: "statuses",
                    description: "Количество задач по статусам",
                    properties: [
                        new OA\Property(
                            property: "failed",
                            description: "Количество неудачных задач",
                            type: "integer",
                            example: 10
                        ),
                        new OA\Property(
                            property: "queued",
                            description: "Количество задач в очереди",
                            type: "integer",
                            example: 50
                        ),
                        new OA\Property(
                            property: "done",
                            description: "Количество выполненных задач",
                            type: "integer",
                            example: 90
                        )
                    ],
                    type: "object"
                ),
                new OA\Property(
                    property: "tasks",
                    description: "Пагинированный список задач",
                    ref: self::PAGINATION_REF
                )
            ],
            type: "object"
        ),
        new OA\Property(
            property: "message",
            description: "Сообщение о результате операции",
            type: "string",
            example: "Список задач получен"
        )
    ],
    type: "object"
)]

final class TaskResponseSchema
{
    public const string TASK_INFO_RESPONSE_SCHEMA = 'TaskInfoResponse';
    public const string ERROR_RESPONSE_SCHEMA = 'TaskErrorResponse';
    public const string TASK_SCHEMA = 'Task';
    public const string PAGINATION_SCHEMA = 'Pagination';
    public const string TASKS_LIST_RESPONSE_SCHEMA = 'TasksListResponse';

    public const string TASK_INFO_RESPONSE_REF = '#/components/schemas/' . self::TASK_INFO_RESPONSE_SCHEMA;
    public const string ERROR_RESPONSE_REF = '#/components/schemas/' . self::ERROR_RESPONSE_SCHEMA;
    public const string TASK_REF = '#/components/schemas/' . self::TASK_SCHEMA;
    public const string PAGINATION_REF = '#/components/schemas/' . self::PAGINATION_SCHEMA;
    public const string TASKS_LIST_RESPONSE_REF = '#/components/schemas/' . self::TASKS_LIST_RESPONSE_SCHEMA;
}

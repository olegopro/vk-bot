<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TaskInfoResponse',
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
                        type: "object",
                        properties: [
                            new OA\Property(property: "type", type: "string", example: "photo"),
                            new OA\Property(
                                property: "photo",
                                type: "object",
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 457240501),
                                    new OA\Property(property: "album_id", type: "integer", example: -7),
                                    new OA\Property(property: "owner_id", type: "integer", example: 855285323),
                                    new OA\Property(property: "date", type: "integer", example: 1751909007),
                                    new OA\Property(property: "access_key", type: "string", example: "ea4a3b28739afcf1db"),
                                    new OA\Property(property: "post_id", type: "integer", example: 801),
                                    new OA\Property(
                                        property: "sizes",
                                        type: "array",
                                        items: new OA\Items(
                                            type: "object",
                                            properties: [
                                                new OA\Property(property: "height", type: "integer", example: 108),
                                                new OA\Property(property: "type", type: "string", example: "s"),
                                                new OA\Property(property: "width", type: "integer", example: 72),
                                                new OA\Property(property: "url", type: "string", example: "https://sun9-12.userapi.com/...")
                                            ]
                                        )
                                    ),
                                    new OA\Property(property: "text", type: "string", example: ""),
                                    new OA\Property(property: "web_view_token", type: "string", example: "a4edd72463b8e5fa23"),
                                    new OA\Property(property: "has_tags", type: "boolean", example: false),
                                    new OA\Property(
                                        property: "orig_photo",
                                        type: "object",
                                        properties: [
                                            new OA\Property(property: "height", type: "integer", example: 2560),
                                            new OA\Property(property: "type", type: "string", example: "base"),
                                            new OA\Property(property: "url", type: "string", example: "https://sun9-12.userapi.com/..."),
                                            new OA\Property(property: "width", type: "integer", example: 1707)
                                        ]
                                    )
                                ]
                            )
                        ]
                    )
                ),
                new OA\Property(
                    property: "likes",
                    description: "Информация о лайках",
                    properties: [
                        new OA\Property(property: "can_like", type: "integer", description: "Может ли текущий пользователь поставить лайк (1 - да, 0 - нет)", example: 0),
                        new OA\Property(property: "count", type: "integer", description: "Количество лайков", example: 1),
                        new OA\Property(property: "user_likes", type: "integer", description: "Поставил ли текущий пользователь лайк (1 - да, 0 - нет)", example: 1),
                        new OA\Property(property: "can_publish", type: "integer", description: "Может ли текущий пользователь сделать репост (1 - да, 0 - нет)", example: 1),
                        new OA\Property(property: "repost_disabled", type: "boolean", description: "Отключен ли репост", example: false)
                    ],
                    type: "object"
                ),
                new OA\Property(
                    property: "liked_users",
                    description: "Пользователи, поставившие лайки",
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/VkUser")
                ),
                new OA\Property(
                    property: "account_id",
                    description: "ID аккаунта",
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
    schema: 'TaskErrorResponse',
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
    schema: 'Task',
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
            example: null,
            nullable: true
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
    schema: 'Pagination',
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
            items: new OA\Items(ref: '#/components/schemas/Task')
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
            example: "http://localhost/api/tasks?page=2",
            nullable: true
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
            example: null,
            nullable: true
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
                        example: "http://localhost:8080/api/tasks?page=1",
                        nullable: true
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
    schema: 'TasksListResponse',
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
                    ref: '#/components/schemas/Pagination',
                    description: "Пагинированный список задач"
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

#[OA\Schema(
    schema: 'DeleteSuccessResponse',
    description: "Ответ об успешном удалении задач",
    properties: [
        new OA\Property(
            property: "success",
            description: "Статус успешности запроса",
            type: "boolean",
            example: true
        ),
        new OA\Property(
            property: "message",
            description: "Сообщение о результате операции",
            type: "string",
            example: "Задачи успешно удалены"
        )
    ],
    type: "object"
)]

final class TaskResponseSchema
{
}

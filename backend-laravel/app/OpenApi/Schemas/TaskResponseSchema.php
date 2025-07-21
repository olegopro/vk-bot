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

final class TaskResponseSchema
{
    public const string TASK_INFO_RESPONSE_SCHEMA = 'TaskInfoResponse';
    public const string ERROR_RESPONSE_SCHEMA = 'TaskErrorResponse';

    public const string TASK_INFO_RESPONSE_REF = '#/components/schemas/' . self::TASK_INFO_RESPONSE_SCHEMA;
    public const string ERROR_RESPONSE_REF = '#/components/schemas/' . self::ERROR_RESPONSE_SCHEMA;
}

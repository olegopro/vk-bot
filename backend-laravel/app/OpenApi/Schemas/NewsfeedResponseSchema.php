<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NewsfeedResponseSchema',
    description: "Схема ответа для новостной ленты аккаунта",
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
            example: "Получены новые данные ленты"
        ),
        new OA\Property(
            property: "data",
            description: "Данные новостной ленты",
            properties: [
                new OA\Property(
                    property: "items",
                    description: "Список элементов новостной ленты",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: "type",
                                description: "Тип элемента новостной ленты",
                                type: "string",
                                example: "post"
                            ),
                            new OA\Property(
                                property: "source_id",
                                description: "ID источника публикации",
                                type: "integer",
                                example: 3757268
                            ),
                            new OA\Property(
                                property: "date",
                                description: "Дата публикации в формате timestamp",
                                type: "integer",
                                example: 1754168183
                            ),
                            new OA\Property(
                                property: "post_id",
                                description: "ID поста",
                                type: "integer",
                                example: 4401
                            ),
                            new OA\Property(
                                property: "owner_id",
                                description: "ID владельца поста",
                                type: "integer",
                                example: 3757268
                            ),
                            new OA\Property(
                                property: "text",
                                description: "Текст поста",
                                type: "string",
                                example: ""
                            ),
                            new OA\Property(
                                property: "attachments",
                                description: "Прикрепления к посту",
                                type: "array",
                                items: new OA\Items(type: "object")
                            ),
                            new OA\Property(
                                property: "likes",
                                description: "Информация о лайках",
                                properties: [
                                    new OA\Property(
                                        property: "can_like",
                                        description: "Можно ли поставить лайк",
                                        type: "integer",
                                        example: 1
                                    ),
                                    new OA\Property(
                                        property: "count",
                                        description: "Количество лайков",
                                        type: "integer",
                                        example: 0
                                    ),
                                    new OA\Property(
                                        property: "user_likes",
                                        description: "Поставил ли пользователь лайк",
                                        type: "integer",
                                        example: 0
                                    )
                                ],
                                type: "object"
                            ),
                            new OA\Property(
                                property: "reposts",
                                description: "Информация о репостах",
                                properties: [
                                    new OA\Property(
                                        property: "count",
                                        description: "Количество репостов",
                                        type: "integer",
                                        example: 0
                                    ),
                                    new OA\Property(
                                        property: "user_reposted",
                                        description: "Сделал ли пользователь репост",
                                        type: "integer",
                                        example: 0
                                    )
                                ],
                                type: "object"
                            ),
                            new OA\Property(
                                property: "comments",
                                description: "Информация о комментариях",
                                properties: [
                                    new OA\Property(
                                        property: "can_post",
                                        description: "Можно ли комментировать",
                                        type: "integer",
                                        example: 0
                                    ),
                                    new OA\Property(
                                        property: "count",
                                        description: "Количество комментариев",
                                        type: "integer",
                                        example: 0
                                    )
                                ],
                                type: "object"
                            )
                        ],
                        type: "object"
                    )
                ),
                new OA\Property(
                    property: "profiles",
                    description: "Список профилей пользователей",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: "id",
                                description: "ID пользователя",
                                type: "integer",
                                example: 3757268
                            ),
                            new OA\Property(
                                property: "first_name",
                                description: "Имя пользователя",
                                type: "string",
                                example: "Анастасия"
                            ),
                            new OA\Property(
                                property: "last_name",
                                description: "Фамилия пользователя",
                                type: "string",
                                example: "Смирнова"
                            ),
                            new OA\Property(
                                property: "screen_name",
                                description: "Короткое имя пользователя",
                                type: "string",
                                example: "nastenchka"
                            ),
                            new OA\Property(
                                property: "photo_50",
                                description: "URL аватара 50x50",
                                type: "string",
                                format: "url"
                            ),
                            new OA\Property(
                                property: "photo_100",
                                description: "URL аватара 100x100",
                                type: "string",
                                format: "url"
                            )
                        ],
                        type: "object"
                    )
                ),
                new OA\Property(
                    property: "groups",
                    description: "Список групп",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: "id",
                                description: "ID группы",
                                type: "integer",
                                example: 23064236
                            ),
                            new OA\Property(
                                property: "name",
                                description: "Название группы",
                                type: "string",
                                example: "Четкие Приколы"
                            ),
                            new OA\Property(
                                property: "screen_name",
                                description: "Короткое имя группы",
                                type: "string",
                                example: "ilikes"
                            ),
                            new OA\Property(
                                property: "photo_50",
                                description: "URL аватара группы 50x50",
                                type: "string",
                                format: "url"
                            ),
                            new OA\Property(
                                property: "photo_100",
                                description: "URL аватара группы 100x100",
                                type: "string",
                                format: "url"
                            )
                        ],
                        type: "object"
                    )
                ),
                new OA\Property(
                    property: "next_from",
                    description: "Маркер для получения следующей страницы",
                    type: "string",
                    example: "news_feed~3AA2ASgCBQPS8qgg5_-223862623_21815:1632147324:40"
                )
            ],
            type: "object"
        )
    ],
    type: "object"
)]

final class NewsfeedResponseSchema {}

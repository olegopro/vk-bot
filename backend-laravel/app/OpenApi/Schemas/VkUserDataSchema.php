<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VkUserData',
    description: "Данные пользователя ВКонтакте с расширенной информацией",
    properties: [
        new OA\Property(
            property: "id",
            description: "ID пользователя",
            type: "integer",
            example: 123456789
        ),
        new OA\Property(
            property: "first_name",
            description: "Имя пользователя",
            type: "string",
            example: "Иван"
        ),
        new OA\Property(
            property: "last_name",
            description: "Фамилия пользователя",
            type: "string",
            example: "Иванов"
        ),
        new OA\Property(
            property: "screen_name",
            description: "Короткое имя (никнейм) пользователя",
            type: "string",
            example: "ivanov",
            nullable: true
        ),
        new OA\Property(
            property: "photo_50",
            description: "URL аватара 50x50",
            type: "string",
            example: "https://sun9-12.userapi.com/...",
            nullable: true
        ),
        new OA\Property(
            property: "photo_100",
            description: "URL аватара 100x100",
            type: "string",
            example: "https://sun9-12.userapi.com/...",
            nullable: true
        ),
        new OA\Property(
            property: "photo_200",
            description: "URL аватара 200x200",
            type: "string",
            example: "https://sun9-12.userapi.com/...",
            nullable: true
        ),
        new OA\Property(
            property: "status",
            description: "Статус пользователя",
            type: "string",
            example: "Хорошее настроение!",
            nullable: true
        ),
        new OA\Property(
            property: "last_seen",
            description: "Информация о последнем визите",
            type: "object",
            nullable: true
        ),
        new OA\Property(
            property: "followers_count",
            description: "Количество подписчиков",
            type: "integer",
            example: 150,
            nullable: true
        ),
        new OA\Property(
            property: "city",
            description: "Информация о городе",
            type: "object",
            nullable: true
        ),
        new OA\Property(
            property: "online",
            description: "Статус онлайн",
            type: "integer",
            example: 1,
            nullable: true
        ),
        new OA\Property(
            property: "bdate",
            description: "Дата рождения",
            type: "string",
            example: "15.10.1990",
            nullable: true
        ),
        new OA\Property(
            property: "country",
            description: "Информация о стране",
            type: "object",
            nullable: true
        ),
        new OA\Property(
            property: "sex",
            description: "Пол пользователя (1 - женский, 2 - мужской)",
            type: "integer",
            example: 2,
            nullable: true
        )
    ],
    type: "object"
)]

#[OA\Schema(
    schema: 'VkUserDataResponse',
    description: "Ответ с данными пользователя ВКонтакте",
    allOf: [
        new OA\Schema(ref: '#/components/schemas/BaseSuccessResponse'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: "message",
                    description: "Сообщение о результате операции",
                    type: "string",
                    example: "Информация о профиле получена"
                ),
                new OA\Property(
                    property: "data",
                    description: "Данные пользователя ВКонтакте",
                    ref: '#/components/schemas/VkUserData'
                )
            ],
            type: "object"
        )
    ]
)]

final class VkUserDataSchema
{
}

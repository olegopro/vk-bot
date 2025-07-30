<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'VkUser',
    description: "Пользователь ВКонтакте",
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
        )
    ],
    type: "object"
)]
final class VkUserSchema
{
}

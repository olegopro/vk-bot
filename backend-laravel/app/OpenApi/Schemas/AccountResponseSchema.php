<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AccountListResponse',
    description: "Ответ со списком аккаунтов ВКонтакте",
    allOf: [
        new OA\Schema(ref: '#/components/schemas/BaseSuccessResponse'),
        new OA\Schema(
            properties: [
                new OA\Property(
                    property: "message",
                    description: "Сообщение о результате операции",
                    type: "string",
                    example: "Список аккаунтов получен"
                ),
                new OA\Property(
                    property: "data",
                    description: "Массив аккаунтов ВКонтакте",
                    type: "array",
                    items: new OA\Items(ref: '#/components/schemas/AccountModel')
                )
            ],
            type: "object"
        )
    ]
)]
final class AccountResponseSchema {}

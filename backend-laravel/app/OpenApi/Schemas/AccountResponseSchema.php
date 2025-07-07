<?php

namespace App\OpenApi\Schemas;

use App\Models\Account;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: self::ACCOUNT_LIST_RESPONSE_SCHEMA,
    description: "Ответ со списком аккаунтов ВКонтакте",
    properties: [
        new OA\Property(
            property: "success",
            description: "Статус успешности запроса",
            type: "boolean",
            example: true
        ),
        new OA\Property(
            property: "data",
            description: "Массив аккаунтов ВКонтакте",
            type: "array",
            items: new OA\Items(ref: Account::class)
        ),
        new OA\Property(
            property: "message",
            description: "Сообщение о результате операции",
            type: "string",
            example: "Список аккаунтов получен"
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
            example: "Произошла ошибка при получении данных"
        )
    ],
    type: "object"
)]

final class AccountResponseSchema
{
    public const string ACCOUNT_LIST_RESPONSE_SCHEMA = 'AccountListResponse';
    public const string ERROR_RESPONSE_SCHEMA = 'ErrorResponse';

    public const string ACCOUNT_LIST_RESPONSE_REF = '#/components/schemas/' . self::ACCOUNT_LIST_RESPONSE_SCHEMA;
    public const string ERROR_RESPONSE_REF = '#/components/schemas/' . self::ERROR_RESPONSE_SCHEMA;
}

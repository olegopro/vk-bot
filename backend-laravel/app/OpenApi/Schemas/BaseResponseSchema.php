<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BaseSuccessResponse',
    description: "Базовая схема успешного ответа",
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
            example: "Операция выполнена успешно"
        )
    ],
    type: "object"
)]

#[OA\Schema(
    schema: 'BaseErrorResponse',
    description: "Базовая схема ответа с ошибкой",
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
            example: "Произошла ошибка при выполнении операции"
        )
    ],
    type: "object"
)]

final class BaseResponseSchema {}

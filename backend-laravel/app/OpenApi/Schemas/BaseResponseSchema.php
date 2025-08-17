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
    schema: 'ErrorResponse',
    description: "Схема ответа с ошибкой",
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

#[OA\Schema(
    schema: 'ServerErrorResponse',
    description: "Схема ответа при внутренней ошибке сервера (500)",
    properties: [
        new OA\Property(
            property: "message",
            description: "Сообщение об ошибке",
            type: "string",
            example: "Внутренняя ошибка сервера при обработке запроса"
        ),
        new OA\Property(
            property: "exception",
            description: "Класс исключения",
            type: "string",
            example: "App\\Exceptions\\ServiceException"
        ),
        new OA\Property(
            property: "file",
            description: "Файл, в котором произошла ошибка",
            type: "string",
            example: "/var/www/html/vendor/package/src/Client.php"
        ),
        new OA\Property(
            property: "line",
            description: "Строка, на которой произошла ошибка",
            type: "integer",
            example: 217
        ),
        new OA\Property(
            property: "trace",
            description: "Трассировка стека вызовов",
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "file",
                        description: "Файл в трассировке",
                        type: "string",
                        example: "/var/www/html/vendor/package/src/Client.php"
                    ),
                    new OA\Property(
                        property: "line",
                        description: "Строка в трассировке",
                        type: "integer",
                        example: 185
                    ),
                    new OA\Property(
                        property: "function",
                        description: "Функция в трассировке",
                        type: "string",
                        example: "toException"
                    ),
                    new OA\Property(
                        property: "class",
                        description: "Класс в трассировке",
                        type: "string",
                        example: "ATehnix\\VkClient\\Client"
                    ),
                    new OA\Property(
                        property: "type",
                        description: "Тип вызова",
                        type: "string",
                        example: "::"
                    )
                ]
            )
        )
    ],
    type: "object"
)]
final class BaseResponseSchema {}

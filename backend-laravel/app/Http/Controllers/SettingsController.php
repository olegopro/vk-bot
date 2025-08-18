<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Http\JsonResponse;

/**
 * Контроллер для управления настройками приложения.
 *
 * Предоставляет методы для получения и сохранения глобальных настроек
 * системы, таких как отображение подписчиков, друзей и тайм-аут задач.
 */
final class SettingsController extends Controller
{
    #[OA\Get(
        path: '/api/settings',
        description: 'Возвращает настройки приложения',
        summary: 'Получить настройки приложения',
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успешное получение настроек',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Настройки успешно получены'
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'show_followers',
                                    type: 'boolean',
                                    example: true
                                ),
                                new OA\Property(
                                    property: 'show_friends',
                                    type: 'boolean',
                                    example: true
                                ),
                                new OA\Property(
                                    property: 'task_timeout',
                                    type: 'integer',
                                    example: 30
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time'
                                ),
                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    format: 'date-time'
                                )
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function getSettings(): JsonResponse
    {
        $settings = Settings::first();

        return response()->json([
            'success' => true,
            'data'    => $settings,
            'message' => 'Настройки успешно получены'
        ]);
    }

    #[OA\Post(
        path: '/api/settings/save',
        description: 'Обновляет настройки приложения',
        summary: 'Сохранить настройки приложения',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['show_followers', 'show_friends', 'task_timeout'],
                properties: [
                    new OA\Property(
                        property: 'show_followers',
                        description: 'Отображение подписчиков',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'show_friends',
                        description: 'Отображение друзей',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'task_timeout',
                        description: 'Таймаут задач в секундах',
                        type: 'integer',
                        example: 30
                    )
                ],
                type: 'object'
            )
        ),
        tags: ['Settings'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Настройки успешно сохранены',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Настройки успешно сохранены'
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'integer',
                                    example: 1
                                ),
                                new OA\Property(
                                    property: 'show_followers',
                                    type: 'boolean',
                                    example: true
                                ),
                                new OA\Property(
                                    property: 'show_friends',
                                    type: 'boolean',
                                    example: true
                                ),
                                new OA\Property(
                                    property: 'task_timeout',
                                    type: 'integer',
                                    example: 30
                                ),
                                new OA\Property(
                                    property: 'created_at',
                                    type: 'string',
                                    format: 'date-time'
                                ),
                                new OA\Property(
                                    property: 'updated_at',
                                    type: 'string',
                                    format: 'date-time'
                                )
                            ],
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: false
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Ошибка валидации'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object'
                        )
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function saveSettings(Request $request): JsonResponse
    {
        $settings = Settings::find(1);
        $settings->update([
            'show_followers' => $request->input('show_followers'),
            'show_friends'   => $request->input('show_friends'),
            'task_timeout'   => $request->input('task_timeout')
        ]);

        return response()->json([
            'success' => true,
            'data'    => $settings->fresh(),
            'message' => 'Настройки успешно сохранены'
        ]);
    }
}

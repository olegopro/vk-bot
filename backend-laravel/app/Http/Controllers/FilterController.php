<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filters\VkUserSearchFilter;
use App\Services\VkClientService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Filters",
    description: "API для фильтрации и поиска пользователей ВКонтакте"
)]
class FilterController extends Controller
{
    /**
     * Создает новый экземпляр контроллера.
     *
     * @param VkClientService $vkClient Сервис для работы с API ВКонтакте
     */
    public function __construct(
        private readonly VkClientService $vkClient,
    ) {}

    #[OA\Post(
        path: "/api/filters/search",
        summary: "Поиск пользователей ВКонтакте",
        description: "Выполняет поиск пользователей ВКонтакте с применением различных фильтров",
        tags: ["Filters"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["account_id"],
                properties: [
                    new OA\Property(property: "q", type: "string", maxLength: 255, description: "Поисковый запрос", example: "Иван Петров"),
                    new OA\Property(property: "account_id", type: "integer", description: "ID аккаунта", example: 1),
                    new OA\Property(property: "city_id", type: "integer", description: "ID города для фильтрации", example: 1),
                    new OA\Property(property: "count", type: "integer", minimum: 1, maximum: 1000, description: "Количество результатов", example: 20)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Поиск пользователей выполнен успешно",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "count", type: "integer", description: "Количество найденных пользователей"),
                            new OA\Property(property: "items", type: "array", items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "id", type: "integer", description: "ID пользователя", example: 123456),
                                    new OA\Property(property: "first_name", type: "string", description: "Имя", example: "Иван"),
                                    new OA\Property(property: "last_name", type: "string", description: "Фамилия", example: "Петров"),
                                    new OA\Property(property: "screen_name", type: "string", description: "Короткое имя (домен)", example: "ivan_petrov"),
                                    new OA\Property(property: "photo_200", type: "string", description: "URL фотографии 200px", example: "https://sun9-74.userapi.com/photo.jpg"),
                                    new OA\Property(property: "city", type: "object", properties: [
                                        new OA\Property(property: "id", type: "integer", description: "ID города", example: 1),
                                        new OA\Property(property: "title", type: "string", description: "Название города", example: "Москва")
                                    ]),
                                    new OA\Property(property: "online", type: "integer", description: "Статус онлайн (0 - оффлайн, 1 - онлайн)", example: 1)
                                ]
                            ))
                        ]),
                        new OA\Property(property: "message", type: "string", example: "Поиск пользователей выполнен успешно")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "errors", type: "object", description: "Ошибки валидации")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Внутренняя ошибка сервера",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "error", type: "string", description: "Сообщение об ошибке")
                    ]
                )
            )
        ]
    )]
    public function searchUsers(Request $request): JsonResponse
    {
        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'q'          => 'string|max:255|nullable',
            'account_id' => 'required|integer',
            'city_id'    => 'integer|nullable',
            'count'      => 'integer|min:1|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Создаем и настраиваем фильтр для поиска
            $filter = new VkUserSearchFilter();

            if ($request->has('q')) {
                $filter->setQuery($request->input('q'));
            }

            if ($request->has('city_id')) {
                $filter->setCity($request->input('city_id'));
            }

            // Устанавливаем количество результатов через сеттер
            if ($request->has('count')) {
                $filter->setCount($request->input('count'));
            }

            // Выполняем поиск через VK API
            $response = $this->vkClient->searchUsers($filter, $request->account_id);

            return response()->json([
                'success' => true,
                'data'    => $response['response'] ?? [],
                'message' => 'Поиск пользователей выполнен успешно'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/filters/users-by-city",
        summary: "Получение пользователей по городу",
        description: "Получает список пользователей ВКонтакте по ID города",
        tags: ["Filters"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["city_id", "account_id"],
                properties: [
                    new OA\Property(property: "city_id", type: "integer", description: "ID города", example: 1),
                    new OA\Property(property: "account_id", type: "integer", description: "ID аккаунта", example: 1),
                    new OA\Property(property: "count", type: "integer", description: "Количество пользователей (по умолчанию 10)", example: 10)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Список пользователей получен успешно",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "domains", type: "array", items: new OA\Items(type: "string", description: "Домен пользователя (screen_name)", example: "user123")),
                            new OA\Property(property: "count", type: "integer", description: "Количество найденных пользователей", example: 5)
                        ]),
                        new OA\Property(property: "message", type: "string", example: "Найдено пользователей: 5")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "errors", type: "object", description: "Ошибки валидации")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Внутренняя ошибка сервера",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "error", type: "string", description: "Сообщение об ошибке")
                    ]
                )
            )
        ]
    )]
    public function getUsersByCity(Request $request): JsonResponse
    {
        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'city_id'    => 'required|integer',
            'account_id' => 'required|integer',
            'count'      => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Создаем и настраиваем фильтр для поиска
            $filter = new VkUserSearchFilter();
            $filter->setCity($request->input('city_id'));

            // Устанавливаем количество результатов через сеттер
            $count = $request->input('count', 10);
            $filter->setCount($count);

            // Выполняем поиск через VK API
            $response = $this->vkClient->searchUsers($filter, $request->account_id);

            // Извлекаем screen_name (domains) пользователей
            $domains = [];
            if (!empty($response['response']['items'])) {
                foreach ($response['response']['items'] as $user) {
                    $domains[] = $user['screen_name'];
                }
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'domains' => $domains,
                    'count'   => count($domains)
                ],
                'message' => 'Найдено пользователей: ' . count($domains)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: "/api/filters/cities",
        summary: "Поиск городов",
        description: "Получает список городов по поисковому запросу через API ВКонтакте",
        tags: ["Filters"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["q"],
                properties: [
                    new OA\Property(property: "q", type: "string", minLength: 2, description: "Поисковый запрос (минимум 2 символа)", example: "Москва"),
                    new OA\Property(property: "country_id", type: "integer", description: "ID страны (по умолчанию 1 - Россия)", example: 1),
                    new OA\Property(property: "count", type: "integer", maximum: 1000, description: "Количество результатов (максимум 1000)", example: 100)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Список городов получен успешно",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", type: "object", properties: [
                            new OA\Property(property: "count", type: "integer", description: "Количество найденных городов"),
                            new OA\Property(property: "items", type: "array", items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "id", type: "integer", description: "ID города", example: 1),
                                    new OA\Property(property: "title", type: "string", description: "Название города", example: "Москва"),
                                    new OA\Property(property: "area", type: "string", description: "Область", example: "Московская область"),
                                    new OA\Property(property: "region", type: "string", description: "Регион", example: "Центральный федеральный округ")
                                ]
                            ))
                        ]),
                        new OA\Property(property: "message", type: "string", example: "Список городов получен")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "errors", type: "object", description: "Ошибки валидации")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Внутренняя ошибка сервера",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "error", type: "string", description: "Сообщение об ошибке")
                    ]
                )
            )
        ]
    )]
    public function getCities(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q'          => 'required|string|min:2',
            'country_id' => 'integer',
            'count'      => 'integer|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $response = $this->vkClient->getCities(
                $request->input('q'),
                $request->input('country_id', 1),
                $request->input('count', 100)
            );

            return response()->json([
                'success' => true,
                'data'    => $response['response'] ?? [],
                'message' => 'Список городов получен'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}

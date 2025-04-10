<?php

namespace App\Http\Controllers;

use App\Filters\VkSearchFilter;
use App\Services\VkClientService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер для поиска пользователей VK с применением различных фильтров.
 *
 * Этот контроллер предоставляет методы для поиска пользователей ВКонтакте
 * через API с возможностью фильтрации по различным параметрам, таким как:
 * - поисковый запрос
 * - возраст
 * - город
 * - пол
 * и другие параметры, поддерживаемые API ВКонтакте.
 *
 * Также поддерживает создание задач на основе найденных пользователей.
 */
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

    /**
     * Выполняет поиск пользователей ВКонтакте с применением фильтров.
     *
     * Метод принимает параметры поиска, валидирует их и выполняет
     * поиск пользователей через API ВКонтакте. Текущая реализация
     * поддерживает базовую фильтрацию по поисковому запросу.
     *
     * @param Request $request HTTP запрос, содержащий параметры:
     *  - q: string - поисковый запрос (обязательный)
     *  - account_id: int - ID аккаунта для выполнения поиска (обязательный)
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'q'          => 'string|max:255|nullable',
            'account_id' => 'required|integer',
            'city_id'    => 'integer|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // Создаем и настраиваем фильтр для поиска
            $filter = new VkSearchFilter();
            $filter->setQuery($request->q);

            if ($request->city_id) {
                $filter->setCity($request->city_id);
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

    /**
     * Находит пользователей по ID города и возвращает их screen_name (domains).
     *
     * @param Request $request HTTP запрос с параметрами:
     *  - city_id: int - ID города для поиска (обязательный)
     *  - account_id: int - ID аккаунта для выполнения поиска (обязательный)
     *  - count: int - количество пользователей для поиска (опциональный, по умолчанию 10)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersByCity(Request $request)
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
            $filter = new VkSearchFilter();
            $filter->setCity($request->city_id);

            // Устанавливаем количество результатов, если оно передано
            $count = $request->input('count', 10);

            // Выполняем поиск через VK API
            $response = $this->vkClient->searchUsers($filter, $request->account_id, $count);

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

    /**
     * Получает список городов по поисковому запросу.
     *
     * Метод делает запрос к API VK для получения списка городов,
     * соответствующих поисковому запросу. Поддерживает фильтрацию
     * по стране и ограничение количества результатов.
     *
     * @param Request $request HTTP запрос, содержащий параметры:
     *  - q: string - поисковый запрос (обязательный, минимум 2 символа)
     *  - country_id: int - ID страны (по умолчанию 1 - Россия)
     *  - count: int - количество результатов (максимум 1000)
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities(Request $request)
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
                $request->q,
                $request->get('country_id', 1),
                $request->get('count', 100)
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

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Repositories\AccountRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use ATehnix\VkClient\Exceptions\VkException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Контроллер для управления аккаунтами.
 *
 * Этот класс предоставляет методы для взаимодействия с данными аккаунтов,
 * включая получение информации об аккаунтах, друзьях, подписчиках и группах.
 */
final class AccountController extends Controller
{
    public function __construct(
        private readonly LoggingServiceInterface    $loggingService,
        private readonly VkClientService            $vkClient,
        private readonly AccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Получить данные аккаунта по ID.
     *
     * @param array|string $ids Идентификатор(ы) аккаунта(ов).
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    #[OA\Get(
        path: '/account/data/{id}',
        description: 'Получает расширенные данные пользователя ВКонтакте по его ID',
        summary: 'Получить данные аккаунта',
        tags: ['Account'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID пользователя ВКонтакте',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '9121607')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные пользователя успешно получены',
                content: new OA\JsonContent(ref: '#/components/schemas/VkUserDataResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Некорректный запрос',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')
            )
        ]
    )]
    public function fetchAccountData(array|string $ids): JsonResponse
    {
        return response()->json(VkClient::fetchAccountData($ids));
    }

    /**
     * Получить данные группы по ID.
     *
     * @param int $id Идентификатор группы.
     * @return JsonResponse
     * @throws VkException
     */
    public function fetchGroupData(int $id): JsonResponse
    {
        return response()->json(VkClient::fetchGroupData($id));
    }

    /**
     * Получить подписчиков аккаунта.
     *
     * @param int $id Идентификатор аккаунта.
     * @param int $limit Лимит количества подписчиков для возврата.
     * @return JsonResponse
     * @throws VkException
     */
    public function fetchAccountFollowers(int $id, int $limit = 6): JsonResponse
    {
        $response = VkClient::fetchAccountFollowers($id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Список подписчиков получен',
            'data'    => $response['response']['items'] ?? []
        ]);
    }

    /**
     * Получить друзей аккаунта.
     *
     * @param int $id Идентификатор аккаунта.
     * @param int $limit Лимит количества друзей для возврата.
     * @return JsonResponse
     * @throws VkException
     */
    public function fetchAccountFriends(int $id, int $limit = 6): JsonResponse
    {
        $response = VkClient::fetchAccountFriends($id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Список друзей получен',
            'data'    => $response['response']['items'] ?? []
        ]);
    }

    /**
     * Получить количество друзей аккаунта.
     *
     * @param int $accountId Идентификатор аккаунта для запроса.
     * @param string $ownerId Идентификатор владельца аккаунта.
     * @return JsonResponse
     * @throws VkException
     */
    public function fetchAccountCountFriends(int $accountId, string $ownerId): JsonResponse
    {
        $accessToken = $this->accountRepository->getAccessTokenByAccountID($accountId);
        $response = VkClient::fetchAccountCountFriends($accountId, $ownerId, $accessToken);

        return response()->json([
            'success' => true,
            'data'    => $response,
            'message' => 'Количество друзей аккаунта получено'
        ]);
    }

    /**
     * Получить новостную ленту аккаунта.
     *
     * @param \Illuminate\Http\Request $request Запрос, содержащий параметры для получения новостной ленты.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    #[OA\Post(
        path: '/account/newsfeed',
        description: 'Получает новостную ленту аккаунта ВКонтакте',
        summary: 'Получить новостную ленту аккаунта',
        requestBody: new OA\RequestBody(
            description: 'Параметры для получения новостной ленты',
            required: true,
            content: new OA\JsonContent(
                required: ['account_id'],
                properties: [
                    new OA\Property(
                        property: 'account_id',
                        description: 'ID аккаунта',
                        type: 'integer',
                        example: 9121607
                    ),
                    new OA\Property(
                        property: 'start_from',
                        description: 'Маркер для пагинации (не обязательный)',
                        type: 'string',
                        example: 'news_feed~3AA2ASgCBQPS8qggoQSUAdLyq-223862623_21815:1632147324:40',
                        nullable: true
                    )
                ],
                type: 'object'
            )
        ),
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Новостная лента успешно получена',
                content: new OA\JsonContent(ref: '#/components/schemas/NewsfeedResponseSchema')
            ),
            new OA\Response(
                response: 400,
                description: 'Некорректный запрос',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: '#/components/schemas/ServerErrorResponse')
            )
        ]
    )]
    public function fetchAccountNewsfeed(Request $request)
    {
        return response()->json(VkClient::fetchAccountNewsfeed(
            $request->input('account_id'),
            $request->input('start_from'),
            $this->loggingService
        ));
    }

    /**
     * Добавить лайк к посту.
     *
     * @param \Illuminate\Http\Request $request Запрос, содержащий данные для добавления лайка.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    public function addLike(Request $request): JsonResponse
    {
        $ownerId = $request->input('owner_id');
        $itemId = $request->input('item_id');
        $accountId = $request->input('account_id');
        $accessToken = $this->vkClient->getAccessTokenByAccountID($accountId);

        return response()->json(
            VkClient::addLike(
                $ownerId,
                $itemId,
                $accessToken,
                $this->loggingService
            )
        );
    }
}

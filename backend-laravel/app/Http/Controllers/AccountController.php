<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikeToPost;
use App\Models\Account;
use App\Models\Task;
use App\OpenApi\Schemas\AccountResponseSchema;
use App\OpenApi\Schemas\VkUserDataSchema;
use App\Repositories\AccountRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use ATehnix\VkClient\Exceptions\VkException;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     * @param string|array $ids Идентификатор(ы) аккаунта(ов).
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    #[OA\Get(
        path: '/api/account/data/{id}',
        description: 'Получает расширенные данные пользователя ВКонтакте по его ID',
        summary: 'Получить данные аккаунта',
        tags: ['Account'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID пользователя ВКонтакте',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', example: '123456789')
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
                content: new OA\JsonContent(ref: '#/components/schemas/BaseErrorResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Внутренняя ошибка сервера',
                content: new OA\JsonContent(ref: '#/components/schemas/BaseErrorResponse')
            )
        ]
    )]
    public function fetchAccountData($ids)
    {
        return response()->json(VkClient::fetchAccountData($ids));
    }

    /**
     * Получить данные группы по ID.
     *
     * @param string $id Идентификатор группы.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    public function fetchGroupData($id)
    {
        return response()->json(VkClient::fetchGroupData($id));
    }

    /**
     * Получить подписчиков аккаунта.
     *
     * @param string $id Идентификатор аккаунта.
     * @param int $limit Лимит количества подписчиков для возврата.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    public function fetchAccountFollowers($id, $limit = 6)
    {
        $response = VkClient::fetchAccountFollowers($id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Список подписчиков получен',
            'data' => $response['response']['items'] ?? []
        ]);
    }

    /**
     * Получить друзей аккаунта.
     *
     * @param string $id Идентификатор аккаунта.
     * @param int $limit Лимит количества друзей для возврата.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    public function fetchAccountFriends($id, $limit = 6)
    {
        $response = VkClient::fetchAccountFriends($id, $limit);

        return response()->json([
            'success' => true,
            'message' => 'Список друзей получен',
            'data' => $response['response']['items'] ?? []
        ]);
    }

    /**
     * Получить количество друзей аккаунта.
     *
     * @param string $accountId Идентификатор аккаунта для запроса.
     * @param string $ownerId Идентификатор владельца аккаунта.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    public function fetchAccountCountFriends($accountId, $ownerId)
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
     * Получить информацию об аккаунте.
     *
     * @param string $access_token Токен доступа аккаунта.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    public function fetchAccountInfo($access_token)
    {
        return response()->json(VkClient::fetchAccountInfo($access_token));
    }

    /**
     * Получить новостную ленту аккаунта.
     *
     * @param \Illuminate\Http\Request $request Запрос, содержащий параметры для получения новостной ленты.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
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
    public function addLike(Request $request)
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

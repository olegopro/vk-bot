<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikeToPost;
use App\Models\Account;
use App\Models\Task;
use App\OpenApi\Schemas\AccountResponseSchema;
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
     * Получить список всех аккаунтов.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/account/all-accounts',
        description: 'Возвращает список всех добавленных аккаунтов ВКонтакте с их основной информацией',
        summary: 'Получить все аккаунты ВКонтакте',
        tags: ['Accounts']
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешное получение списка аккаунтов',
        content: new OA\JsonContent(ref: AccountResponseSchema::ACCOUNT_LIST_RESPONSE_REF)
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: AccountResponseSchema::ERROR_RESPONSE_REF)
    )]
    public function fetchAllAccounts()
    {
        return response()->json(VkClient::fetchAllAccounts());
    }

    /**
     * Получить данные аккаунта по ID.
     *
     * @param string|array $ids Идентификатор(ы) аккаунта(ов).
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
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
        return response()->json(VkClient::fetchAccountFollowers($id, $limit));
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
        return response()->json(VkClient::fetchAccountFriends($id, $limit));
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
     * Установить данные аккаунта.
     *
     * @param \Illuminate\Http\Request $request Запрос, содержащий данные аккаунта для обновления.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    #[OA\Post(
        path: '/account/add',
        description: 'Устанавливает и сохраняет данные аккаунта ВКонтакте по токену доступа',
        summary: 'Добавить аккаунт ВКонтакте',
        tags: ['Accounts']
    )]
    #[OA\RequestBody(
        description: 'Данные для добавления аккаунта',
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'access_token',
                    description: 'Токен доступа к API ВКонтакте',
                    type: 'string',
                    example: 'vk1.a.abc123def456...'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные аккаунта успешно установлены',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'success',
                    description: 'Статус успешности запроса',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'data',
                    ref: Account::class,
                    description: 'Данные добавленного аккаунта'
                ),
                new OA\Property(
                    property: 'message',
                    description: 'Сообщение о результате операции',
                    type: 'string',
                    example: 'Аккаунт успешно добавлен'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Ошибка валидации или неверный токен',
        content: new OA\JsonContent(ref: AccountResponseSchema::ERROR_RESPONSE_REF)
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: AccountResponseSchema::ERROR_RESPONSE_REF)
    )]
    public function setAccountData(Request $request)
    {
        return response()->json(VkClient::setAccountData($request['access_token'], $this->accountRepository));
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

    /**
     * Удалить аккаунт.
     *
     * @param string $id Идентификатор аккаунта для удаления.
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Delete(
        path: '/account/delete-account/{id}',
        description: 'Удаляет аккаунт ВКонтакте из системы по его идентификатору',
        summary: 'Удалить аккаунт ВКонтакте',
        tags: ['Accounts']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'Идентификатор аккаунта для удаления',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'integer',
            example: 123456789
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Аккаунт успешно удален',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'success',
                    description: 'Статус успешности запроса',
                    type: 'boolean',
                    example: true
                ),
                new OA\Property(
                    property: 'message',
                    description: 'Сообщение о результате операции',
                    type: 'string',
                    example: 'Аккаунт успешно удален'
                )
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Аккаунт не найден',
        content: new OA\JsonContent(ref: AccountResponseSchema::ERROR_RESPONSE_REF)
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: AccountResponseSchema::ERROR_RESPONSE_REF)
    )]
    public function deleteAccount($id)
    {
        return response()->json(VkClient::deleteAccount($id));
    }
}

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
final class AccountsController extends Controller
{
    public function __construct(
        private readonly AccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Получить список всех аккаунтов.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Get(
        path: '/accounts/all-accounts',
        description: 'Возвращает список всех добавленных аккаунтов ВКонтакте с их основной информацией',
        summary: 'Получить все аккаунты ВКонтакте',
        tags: ['Accounts']
    )]
    #[OA\Response(
        response: 200,
        description: 'Успешное получение списка аккаунтов',
        content: new OA\JsonContent(ref: '#/components/schemas/AccountListResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/BaseErrorResponse')
    )]
    public function fetchAllAccounts()
    {
        return response()->json(VkClient::fetchAllAccounts());
    }

    /**
     * Установить данные аккаунта.
     *
     * @param \Illuminate\Http\Request $request Запрос, содержащий данные аккаунта для обновления.
     * @return \Illuminate\Http\JsonResponse
     * @throws VkException
     */
    #[OA\Post(
        path: '/accounts/add',
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
                    ref: '#/components/schemas/AccountModel',
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
        response: 500,
        description: 'Внутренняя ошибка сервера',
        content: new OA\JsonContent(ref: '#/components/schemas/BaseErrorResponse')
    )]
    public function setAccountData(Request $request)
    {
        return response()->json(VkClient::setAccountData($request['access_token'], $this->accountRepository));
    }

    /**
     * Удалить аккаунт.
     *
     * @param string $id Идентификатор аккаунта для удаления.
     * @return \Illuminate\Http\JsonResponse
     */
    #[OA\Delete(
        path: '/accounts/delete-account/{id}',
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
    public function deleteAccount($id)
    {
        return response()->json(VkClient::deleteAccount($id));
    }
}

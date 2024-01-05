<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed request(string $method, array $parameters = [], string|null $token = null)
 * @method static array getAccountData(mixed $ids)
 * @method static array getGroupData(mixed $id)a
 * @method static array getAccountFollowers(mixed $id, int $limit)
 * @method static array getAccountFriends(mixed $id, int $limit)
 * @method static array getAccountCountFriends(string $accountId, string $ownerId, string $token)
 * @method static array getAccountInfo(string $token)
 * @method static array setAccountData(string $token, $accountRepository)
 * @method static array getAccountNewsfeed(int $accountId, ?string $startFrom, $loggingService)
 * @method static array addLike(int $ownerId, int $itemId, string $accessToken, $loggingService)
 * @method static string getAccessTokenByAccountID(int $account_id)
 * @method static string getScreenNameByToken(string $access_token)
 */
class VkClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'VkClientService'; // Название сервиса в контейнере
    }
}

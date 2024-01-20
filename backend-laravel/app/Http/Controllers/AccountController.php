<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikesToPosts;
use App\Models\Account;
use App\Models\Task;
use App\Repositories\AccountRepositoryInterface;
use App\Services\LoggingServiceInterface;
use App\Services\VkClientService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class AccountController extends Controller
{
    public function __construct(
        private readonly LoggingServiceInterface    $loggingService,
        private readonly VkClientService            $vkClient,
        private readonly AccountRepositoryInterface $accountRepository
    ) {}

    public function allAccounts()
    {
        return response()->json(VkClient::allAccounts());
    }

    public function deleteAccount($id)
    {
        return response()->json(VkClient::deleteAccount($id));
    }

    public function getAccountData($ids)
    {
        return response()->json(VkClient::getAccountData($ids));
    }

    public function getGroupData($id)
    {
        return response()->json(VkClient::getGroupData($id));
    }

    public function getAccountFollowers($id, $limit = 6)
    {
        return response()->json(VkClient::getAccountFollowers($id, $limit));
    }

    public function getAccountFriends($id, $limit = 6)
    {
        return response()->json(VkClient::getAccountFriends($id, $limit));
    }

    public function getAccountCountFriends($accountId, $ownerId)
    {
        $accessToken = $this->accountRepository->getAccessTokenByAccountID($accountId);
        $response = VkClient::getAccountCountFriends($accountId, $ownerId, $accessToken);

        return response()->json([
            'success' => true,
            'data'    => $response,
            'message' => 'Количество друзей аккаунта получено'
        ]);
    }

    public function getAccountInfo($access_token)
    {
        return response()->json(VkClient::getAccountInfo($access_token));
    }

    public function setAccountData(Request $request)
    {
        return response()->json(VkClient::setAccountData($request['access_token'], $this->accountRepository));
    }

    public function getAccountNewsfeed(Request $request)
    {
        return response()->json(VkClient::getAccountNewsfeed(
            $request->input('account_id'),
            $request->input('start_from'),
            $this->loggingService
        ));
    }

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

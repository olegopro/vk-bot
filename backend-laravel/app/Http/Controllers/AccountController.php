<?php

namespace App\Http\Controllers;

use App\Facades\VkClient;
use App\Jobs\addLikeToPost;
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

    public function fetchAllAccounts()
    {
        return response()->json(VkClient::fetchAllAccounts());
    }

    public function fetchAccountData($ids)
    {
        return response()->json(VkClient::fetchAccountData($ids));
    }

    public function fetchGroupData($id)
    {
        return response()->json(VkClient::fetchGroupData($id));
    }

    public function fetchAccountFollowers($id, $limit = 6)
    {
        return response()->json(VkClient::fetchAccountFollowers($id, $limit));
    }

    public function fetchAccountFriends($id, $limit = 6)
    {
        return response()->json(VkClient::fetchAccountFriends($id, $limit));
    }

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

    public function fetchAccountInfo($access_token)
    {
        return response()->json(VkClient::fetchAccountInfo($access_token));
    }

    public function fetchAccountNewsfeed(Request $request)
    {
        return response()->json(VkClient::fetchAccountNewsfeed(
            $request->input('account_id'),
            $request->input('start_from'),
            $this->loggingService
        ));
    }

    public function setAccountData(Request $request)
    {
        return response()->json(VkClient::setAccountData($request['access_token'], $this->accountRepository));
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

    public function deleteAccount($id)
    {
        return response()->json(VkClient::deleteAccount($id));
    }
}

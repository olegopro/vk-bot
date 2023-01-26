<?php

namespace App\Http\Controllers;

use App\Library\VkClient;
use App\Models\Account;
use App\Models\Task;
use DB;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $accounts = Account::all();

        return response($accounts);
    }

    public function accountByTaskId($taskId)
    {
        $account = Task::find($taskId)->account;

        return response($account);
    }

    public function show($id)
    {
        $account = Account::find($id);

        return response($account);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        return Account::destroy($id);
    }

    public function getAccountData($id)
    {
        return (new VkClient())->request('users.get', [
            'user_ids' => $id,
            'fields'   => [
                'photo_200',
                'status',
                'screen_name',
                'last_seen',
                'followers_count',
                'city',
                'online'
            ]
        ]);
    }

    public function getAccountFollowers($id, $limit = 6)
    {
        return (new VkClient())->request('users.getFollowers', [
            'user_id' => $id,
            'count'   => $limit,
            'fields'  => [
                'about',
                'photo_200'
            ]
        ]);
    }

    public function getAccountFriends($id, $limit = 6)
    {
        return (new VkClient())->request('friends.get', [
            'user_id' => $id,
            'count'   => $limit,
            'fields'  => [
                'about',
                'photo_200'
            ]
        ]);
    }

    public function getAccountCountFriends($id)
    {
        return (new VkClient())->request('friends.get', [
            'user_id' => $id,
            'count'   => 1,
        ]);
    }

    public function getAccountInfo($access_token)
    {
        return (new VkClient($access_token))->request('account.getProfileInfo');
    }

    public function setAccountData(Request $request)
    {
        $accountData = $this->getAccountInfo($request['access_token']);

        return Account::create([
            'access_token' => $request['access_token'],
            'account_id'   => $accountData['response']['id'],
            'screen_name'  => $accountData['response']['screen_name'],
            'first_name'   => $accountData['response']['first_name'],
            'last_name'    => $accountData['response']['last_name'],
            'bdate'        => $accountData['response']['bdate']
        ]);
    }

    public function getAccountNewsfeed(Request $request)
    {
        $access_token = $this->getAccessTokenByAccountID($request->input('account_id'));

        return (new VkClient($access_token))->request('newsfeed.get', [
            'filters' => 'post',
            'count'   => 30
        ]);
    }

    private function getAccessTokenByAccountID($account_id)
    {
        return $account = DB::table('accounts')
                            ->where('account_id', $account_id)
                            ->value('access_token');
    }

    public function addLike(Request $request)
    {
        $owner_id = $request->input('owner_id');
        $item_id = $request->input('item_id');

        $access_token = $this->getAccessTokenByAccountID(request()->input('account_id'));

        return (new VkClient($access_token))->request('likes.add', [
            'type'     => 'post',
            'owner_id' => $owner_id,
            'item_id'  => $item_id
        ]);
    }
}

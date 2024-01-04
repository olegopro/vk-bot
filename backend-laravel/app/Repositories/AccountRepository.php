<?php

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountRepository implements AccountRepositoryInterface
{
	public function getAllAccounts()
	{
		return $accounts = Account::all();
	}

	public function createAccount(array $data)
	{
		return Account::create($data);
	}

	public function deleteAccount($id)
	{
		return Account::destroy($id);
	}

	public function getAccessTokenByAccountID($account_id)
	{
		return DB::table('accounts')
		         ->where('account_id', $account_id)
		         ->value('access_token');
	}

	public function getScreenNameByToken($access_token)
	{
		return DB::table('accounts')
		         ->where('access_token', $access_token)
		         ->value('screen_name');
	}
}

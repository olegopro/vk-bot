<?php

namespace App\Repositories;

interface AccountRepositoryInterface
{
	public function getAllAccounts();

	public function createAccount(array $data);

	public function deleteAccount($id);

	public function getAccessTokenByAccountID($account_id);

	public function getScreenNameByToken($access_token);
}

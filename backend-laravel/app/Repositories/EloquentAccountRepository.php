<?php

namespace App\Repositories;

use App\Models\Account; // Предполагается, что у вас есть модель Account

class EloquentAccountRepository implements AccountRepositoryInterface
{
	public function getAll()
	{
		return Account::all();
	}

	public function findById($id)
	{
		return Account::find($id);
	}

	public function create(array $data)
	{
		return Account::create($data);
	}

	public function update($id, array $data)
	{
		$account = Account::find($id);
		if ($account) {
			$account->update($data);
			return $account;
		}
		return null;
	}

	public function delete($id)
	{
		$account = Account::find($id);
		if ($account) {
			return $account->delete();
		}
		return false;
	}
}

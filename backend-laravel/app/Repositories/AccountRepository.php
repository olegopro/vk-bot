<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Класс репозитория для работы с учетными записями.
 * Предоставляет методы для управления учетными записями и доступа к их данным.
 */
class AccountRepository implements AccountRepositoryInterface
{
    /**
     * Получает все учетные записи.
     *
     * @return \Illuminate\Database\Eloquent\Collection Возвращает коллекцию всех учетных записей.
     */
    public function getAllAccounts(): Collection
    {
        return Account::all();
    }

    /**
     * Создает новую учетную запись с указанными данными.
     *
     * @param array $data Данные для создания учетной записи.
     * @return Account|Model Возвращает созданную учетную запись.
     */
    public function createAccount(array $data): Account|Model
    {
        return Account::create($data);
    }

    /**
     * Удаляет учетную запись по идентификатору.
     *
     * @param int $id Идентификатор учетной записи для удаления.
     * @return bool Успешность удаления.
     */
    public function deleteAccount(int $id): bool
    {
        $deletedCount = Account::destroy($id);
    
        return $deletedCount > 0;
    }

    /**
     * Получает токен доступа по идентификатору учетной записи.
     *
     * @param int $account_id Идентификатор учетной записи.
     * @return string|null Токен доступа или null, если учетная запись не найдена.
     */
    public function getAccessTokenByAccountID($account_id): string|null
    {
        $account = Account::where('account_id', $account_id)->first();

        if ($account) {
            return Account::decryptToken($account->access_token);
        }

        return null;
    }

    /**
     * Получает отображаемое имя (screen name) по токену доступа.
     *
     * @param string $access_token Токен доступа.
     * @return string|null Имя экрана или null, если токен не найден.
     */
    public function getScreenNameByToken($access_token): string|null
    {
        $encryptedToken = Account::encryptToken($access_token);
        $account = Account::where('access_token', $encryptedToken)->first();
        
        return $account?->screen_name;
    }
}

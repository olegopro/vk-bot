<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface AccountRepositoryInterface
 *
 * Этот интерфейс определяет контракт для репозитория аккаунтов,
 * обеспечивая абстракцию для взаимодействия с данными аккаунтов.
 */
interface AccountRepositoryInterface
{
    /**
     * Получить все аккаунты.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     * Возвращает пагинатор со всеми аккаунтами.
     */
	public function getAllAccounts();

    /**
     * Создать новый аккаунт с предоставленными данными.
     *
     * @param array $data Данные для создания аккаунта.
     * @return \App\Models\Account
     * Возвращает экземпляр созданного аккаунта.
     */
	public function createAccount(array $data);

    /**
     * Удалить аккаунт по идентификатору.
     *
     * @param mixed $id Идентификатор аккаунта для удаления.
     * @return bool
     * Возвращает true, если аккаунт успешно удален, иначе false.
     */
	public function deleteAccount($id);

    /**
     * Получить токен доступа по идентификатору аккаунта.
     *
     * @param mixed $account_id Идентификатор аккаунта.
     * @return string|null
     * Возвращает токен доступа или null, если аккаунт не найден.
     */
	public function getAccessTokenByAccountID($account_id);

    /**
     * Получить screen name аккаунта по токену доступа.
     *
     * @param string $access_token Токен доступа аккаунта.
     * @return string|null
     * Возвращает screen name аккаунта или null, если аккаунт не найден.
     */
	public function getScreenNameByToken($access_token);
}

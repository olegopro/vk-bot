<?php

namespace App\Repositories;

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
     * @return \Illuminate\Database\Eloquent\Collection Возвращает коллекцию всех учетных записей.
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
     * @param int $id Идентификатор аккаунта для удаления.
     * @return bool
     * Возвращает true, если аккаунт успешно удален, иначе false.
     */
    public function deleteAccount(int $id);

    /**
     * Получить токен доступа по идентификатору аккаунта.
     *
     * @param int $account_id Идентификатор аккаунта.
     * @return string|null
     * Возвращает токен доступа или null, если аккаунт не найден.
     */
    public function getAccessTokenByAccountID(int $account_id);

    /**
     * Получить screen name аккаунта по идентификатору аккаунта.
     *
     * @param int $account_id Идентификатор аккаунта.
     * @return string|null
     * Возвращает screen name аккаунта или null, если аккаунт не найден.
     */
    public function getScreenNameByAccountID(int $account_id);
}

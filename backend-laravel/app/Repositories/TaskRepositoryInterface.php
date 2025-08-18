<?php

namespace App\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Collection;

/**
 * Интерфейс репозитория задач.
 *
 * Определяет методы для управления и взаимодействия с задачами в системе,
 * включая поиск, получение статусов и удаление задач.
 */
interface TaskRepositoryInterface
{
    /**
     * Получает задачи по их статусу.
     *
     * @param string|null $status Статус задач для фильтрации.
     */
	public function getTasksByStatus(string|null $status, $accountId, $perPage);

    /**
     * Поиск задачи по идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     */
	public function findTask(int $taskId);

    /**
     * Получает статус задачи по её идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     */
	public function getTaskStatusById(int $taskId);

    public function countTasksByAccountAndStatus($status, $accountId);

    /**
     * Удаляет выполненную задачу по идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     */
	public function deleteCompletedTask(int $taskId);

    /**
     * Удаляет задачу из очереди по её идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     */
	public function deleteQueuedTask(int $taskId);

    /**
     * Удаляет неуспешную задачу по идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     */
	public function deleteFailedTask(int $taskId);

    /**
     * Очищает очередь задач на основе их статуса.
     *
     * @param string|null $status Статус задач для очистки.
     * @param int|null $accountId Идентификатор аккаунта для дополнительной фильтрации.
     */
	public function clearQueueBasedOnStatus(string|null $status = null, int|null $accountId = null);

    /**
     * Удаляет задачи из очереди на основе их статуса.
     *
     * @param string|null $status Статус задач для удаления.
     */
	public function deleteJobsByStatus(string|null $status);
}

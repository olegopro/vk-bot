<?php

namespace App\Repositories;

use App\Models\Task;
use DB;
use Exception;
use Illuminate\Support\Str;

/**
 * Класс репозитория для работы с задачами.
 * Предоставляет методы для поиска, получения статусов и управления задачами в базе данных.
 */
class TaskRepository implements TaskRepositoryInterface
{
    /**
     * Находит задачу по идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     * @return Task Возвращает экземпляр задачи, если она найдена.
     * @throws Exception Если задача не найдена, выбрасывается исключение.
     */
    public function findTask($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            throw new Exception('Задача не найдена', 404);
        }

        return $task;
    }

    /**
     * Получает задачи по статусу и идентификатору аккаунта.
     *
     * @param string|null $status Статус задачи для фильтрации. Если null, фильтрация не применяется.
     * @param int|null $accountId Идентификатор аккаунта для дополнительной фильтрации. Если null, фильтрация не применяется.
     * @return \Illuminate\Database\Eloquent\Collection Возвращает коллекцию задач, соответствующих критериям фильтрации.
     */
    public function getTaskStatus($status = null, $accountId = null, $perPage = 30)
    {
        $query = Task::query();

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if (!is_null($accountId)) {
            $query->where('account_id', $accountId);
        }

        return $query->simplePaginate($perPage);
    }

    /**
     * Получает статус задачи по её идентификатору.
     *
     * @param int $taskId Идентификатор задачи.
     * @return string|null Статус задачи или null, если задача не найдена.
     */
    public function getTaskStatusById($taskId)
    {
        return Task::where('id', $taskId)->value('status');
    }

    /**
     * Очищает очередь задач на основе статуса и идентификатора аккаунта.
     *
     * @param string|null $status Статус задач для очистки. Если null, очищаются задачи всех статусов.
     * @param int|null $accountId Идентификатор аккаунта для дополнительной фильтрации. Если null, фильтрация не применяется.
     */
    public function clearQueueBasedOnStatus($status = null, $accountId = null)
    {
        switch ($status) {
            case 'done':
                // Удаляем задачи со статусом 'done' с учетом accountId
                $query = Task::where('status', 'done');

                if ($accountId) {
                    $query->where('account_id', $accountId);
                }

                $query->delete();

                break;

            case 'queued':
                // Удаляем задачи со статусом 'pending' с учетом accountId
                $query = Task::where('status', 'queued');

                if ($accountId) {
                    $query->where('account_id', $accountId);
                }

                $this->deleteJobsByStatus('queued', $accountId);
                $query->delete();

                break;

            case 'failed':
                // Удаляем задачи со статусом 'failed' с учетом accountId
                $query = Task::where('status', 'failed');

                if ($accountId) {
                    $query->where('account_id', $accountId);
                }

                $this->deleteJobsByStatus('failed', $accountId);
                $query->delete();

                // Очищаем таблицу failed_jobs с учетом accountId
                if (!$accountId) {
                    DB::table('failed_jobs')->truncate();
                }

                break;

            default:
                // Если указан accountId, удаляем задачи только для этого accountId
                if ($accountId) {
                    // Удаляем задачи только для accountId
                    Task::where('account_id', $accountId)->delete();
                    // Удаляем задачи из jobs для accountId
                    $this->deleteJobsByStatus(null, $accountId);
                } else {
                    // Если accountId не указан, удаляем все задачи
                    Task::query()->truncate();
                    DB::table('jobs')->truncate();
                    DB::table('failed_jobs')->truncate();
                }

                break;
        }
    }

    /**
     * Удаляет завершенную задачу по идентификатору.
     *
     * @param int $taskId Идентификатор задачи для удаления.
     * @return bool|null Количество удаленных записей.
     */
    public function deleteCompletedTask($taskId)
    {
        // Удаление завершенной задачи
        return Task::where('id', $taskId)
                   ->where('status', 'done')
                   ->delete();
    }

    /**
     * Удаляет задачу из очереди по идентификатору.
     *
     * @param int $taskId Идентификатор задачи для удаления из очереди.
     * @throws Exception Если задача не найдена, выбрасывается исключение.
     * @return bool|null Возвращает true, если задача успешно удалена, иначе null.
     */
    public function deleteQueuedTask($taskId)
    {
        // Находим задачу в таблице tasks
        $task = Task::find($taskId);

        if (!$task) {
            throw new Exception('Задача не найдена', 404);
        }

        // Удаляем задачу из очереди jobs, используя job_id из таблицы tasks
        if ($task->job_id) {
            DB::table('jobs')->where('id', $task->job_id)->delete();
        }

        // Затем удаляем задачу из таблицы задач
        return $task->delete();
    }


    /**
     * Удаляет неуспешную задачу по идентификатору.
     *
     * @param int $taskId Идентификатор задачи для удаления.
     * @return bool|null Количество удаленных записей.
     */
    public function deleteFailedTask($taskId)
    {
        // Удаление неуспешной задачи
        return Task::where('id', $taskId)
                   ->where('status', 'failed')
                   ->delete();
    }

    /**
     * Удаляет задачи из таблицы jobs на основе статуса и идентификатора аккаунта.
     *
     * @param string|null $status Статус задач для удаления. Если null, удаляются задачи всех статусов.
     * @param int|null $accountId Идентификатор аккаунта для дополнительной фильтрации. Если null, фильтрация не применяется.
     */
    public function deleteJobsByStatus($status, $accountId = null)
    {
        // Получаем список job_id из таблицы tasks, соответствующих условиям
        $tasks = Task::query()
                     ->when($status, function ($query) use ($status) {
                         // Здесь $query это объект построителя запросов, который Laravel передаёт в функцию.
                         // Мы используем этот объект, чтобы добавить условие where к нашему запросу.
                         // Это ограничение будет применено, если $status не null
                         return $query->where('status', $status);
                     })
                     ->when($accountId, function ($query) use ($accountId) {
                         // И снова Laravel передаёт объект построителя запросов ($query) в функцию.
                         // Если $accountId не null, мы добавляем дополнительное условие where.
                         // Это ограничение будет применено, если $accountId не null
                         return $query->where('account_id', $accountId);
                     })
                     ->pluck('job_id'); // Извлекаем массив идентификаторов заданий (job_id) из записей таблицы tasks,

        // Удаляем задачи из таблицы jobs
        DB::table('jobs')->whereIn('id', $tasks)->delete();
    }
}

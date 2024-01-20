<?php

namespace App\Repositories;

use App\Models\Task;
use DB;
use Exception;
use Illuminate\Support\Str;

class TaskRepository implements TaskRepositoryInterface
{
    public function findTask($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            throw new Exception('Задача не найдена', 404);
        }

        return $task;
    }

    public function getTaskStatus($status = null, $accountId = null)
    {
        $query = Task::query();

        if (!is_null($status)) {
            $query->where('status', $status);
        }

        if (!is_null($accountId)) {
            $query->where('account_id', $accountId);
        }

        return $query->get();
    }

    public function getTaskStatusById($taskId)
    {
        return Task::where('id', $taskId)->value('status');
    }

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

    public function deleteCompletedTask($taskId)
    {
        // Удаление завершенной задачи
        return Task::where('id', $taskId)
                   ->where('status', 'done')
                   ->delete();
    }

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

    public function deleteFailedTask($taskId)
    {
        // Удаление неуспешной задачи
        return Task::where('id', $taskId)
                   ->where('status', 'failed')
                   ->delete();
    }

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

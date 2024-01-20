<?php

namespace App\Repositories;

use App\Models\Task;
use DB;
use Exception;
use Illuminate\Support\Str;

class TaskRepository implements TaskRepositoryInterface
{
    public function taskStatus($status = null, $accountId = null)
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

    public function findTask($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            throw new Exception('Задача не найдена', 404);
        }

        return $task;
    }

    public function getTaskStatusById($taskId)
    {
        return Task::where('id', $taskId)->value('status');
    }

    public function deleteCompletedTask($taskId)
    {
        // Удаление завершенной задачи
        return Task::where('id', $taskId)
                   ->where('status', 'done')
                   ->delete();
    }

    public function deletePendingTask($taskId)
    {
        // Удаление ожидающей задачи
        // Сначала удаляем задачу из очереди
        DB::table('jobs')->orderBy('id')->chunk(100, function ($jobs) use ($taskId) {
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $command = unserialize($payload['data']['command']);
                $commandTaskId = method_exists($command, 'getTask') ? $command->getTask()->id : null;

                if ($commandTaskId === $taskId) {
                    DB::table('jobs')->where('id', $job->id)->delete();
                }
            }
        });

        // Затем удаляем задачу из таблицы задач
        return Task::where('id', $taskId)
                   ->where('status', 'pending')
                   ->delete();
    }

    public function deleteFailedTask($taskId)
    {
        // Удаление неуспешной задачи
        return Task::where('id', $taskId)
                   ->where('status', 'failed')
                   ->delete();
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

                $query->delete();

                $this->deleteJobsByStatus('queued', $accountId);

                break;

            case 'failed':
                // Удаляем задачи со статусом 'failed' с учетом accountId
                $query = Task::where('status', 'failed');

                if ($accountId) {
                    $query->where('account_id', $accountId);
                }

                $query->delete();

                $this->deleteJobsByStatus('failed', $accountId);

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

    public function deleteJobsByStatus($status, $accountId = null)
    {
        // В jobs задача находится со статусом pending, в таблице tasks со статусом queued
        $status = ($status === 'queued') ? 'pending' : $status;

        DB::table('jobs')->orderBy('id')->chunk(100, function ($jobs) use ($status, $accountId) {
            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $command = unserialize($payload['data']['command']);

                // Используем метод getAccountId() для получения account_id, если он доступен
                $commandAccountId = method_exists($command, 'getAccountId')
                    ? $command->getAccountId()
                    : null;

                // Приводим оба account_id к типу int перед сравнением
                $commandAccountId = (int) $commandAccountId;
                $accountId = $accountId !== null ? (int) $accountId : null;

                if ($accountId !== null && $commandAccountId !== $accountId) {
                    // Пропускаем задачу, если account_id не совпадает
                    continue;
                }

                // Если статус не указан, удаляем задачи всех статусов для данного accountId
                if ($status === null || Str::lower($status) === Str::lower($command->getTaskStatus())) {
                    DB::table('jobs')->where('id', $job->id)->delete();
                }
            }
        });
    }

}

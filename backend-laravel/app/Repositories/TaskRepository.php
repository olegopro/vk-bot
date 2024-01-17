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

	public function deleteAllTasks($status)
	{
		if ($status) {
			return Task::where('status', $status)->delete();
		} else {
			Task::truncate();
		}
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

	public function clearQueueBasedOnStatus($status = null)
	{
		switch ($status) {
			case 'done':
				// Удаляем только из таблицы tasks
				Task::where('status', 'done')->delete();
				break;

			case 'pending':
				// Удаляем из таблицы tasks
				Task::where('status', 'pending')->delete();

				// Удаляем также из таблицы jobs
				$this->deleteJobsByStatus('pending');
				break;

			case 'failed':
				// Удаляем задачи со статусом failed из таблицы tasks
				Task::where('status', 'failed')->delete();

				// Очищаем таблицу failed_jobs
				DB::table('failed_jobs')->truncate();
				break;

			default:
				// Удаляем все задачи из tasks и jobs
				Task::truncate();
				DB::table('jobs')->truncate();
				break;
		}
	}

	public function deleteJobsByStatus($status)
	{
		$jobs = DB::table('jobs')->get();
		foreach ($jobs as $job) {
			$payload = json_decode($job->payload, true);
			$command = unserialize($payload['data']['command']);

			if (method_exists($command, 'getTaskStatus')) {
				$taskStatus = $command->getTaskStatus();

				if (Str::lower($taskStatus) === Str::lower($status)) {
					DB::table('jobs')->where('id', $job->id)->delete();
				}
			}
		}
	}
}

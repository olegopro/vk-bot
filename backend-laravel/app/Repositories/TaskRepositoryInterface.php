<?php

namespace App\Repositories;

interface TaskRepositoryInterface
{
	public function taskStatus($status);

	public function findTask($taskId);

	public function getTaskStatusById($taskId);

	public function deleteAllTasks($status);

	public function deleteCompletedTask($taskId);

	public function deletePendingTask($taskId);

	public function deleteFailedTask($taskId);

	public function clearQueueBasedOnStatus($status = null);

	public function deleteJobsByStatus($status);
}
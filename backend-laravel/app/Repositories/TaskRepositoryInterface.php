<?php

namespace App\Repositories;

interface TaskRepositoryInterface
{
	public function getTaskStatus($status);

	public function findTask($taskId);

	public function getTaskStatusById($taskId);

	public function deleteCompletedTask($taskId);

	public function deleteQueuedTask($taskId);

	public function deleteFailedTask($taskId);

	public function clearQueueBasedOnStatus($status = null);

	public function deleteJobsByStatus($status);
}

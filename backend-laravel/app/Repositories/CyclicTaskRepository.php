<?php

namespace App\Repositories;

use App\Models\CyclicTask;

class CyclicTaskRepository implements CyclicTaskRepositoryInterface
{
    public function getCyclicTasks()
    {
        return CyclicTask::all();
    }

    public function deleteCyclicTask($taskId) {
        return CyclicTask::destroy($taskId);
    }
}

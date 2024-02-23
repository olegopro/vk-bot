<?php

namespace App\Repositories;

use App\Models\CyclicTask;

class CyclicTaskRepository implements CyclicTaskRepositoryInterface
{
    public function getCyclicTasks()
    {
        return CyclicTask::all();
    }
}

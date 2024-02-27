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

    public function deleteAllCyclicTasks()
    {
        return CyclicTask::truncate();
    }

    public function pauseCyclicTask($taskId)
    {
        $task = CyclicTask::find($taskId);

        if ($task) {
            $task->status = $task->status === 'active' ? 'pause' : 'active';
            $task->save();

            return [
                'task' => $task,
                'statusChangedTo' => $task->status,
            ];
        }

        return $task;
    }

    public function editCyclicTask($taskId, $data)
    {
        $task = CyclicTask::find($taskId);

        if ($task) {
            $task->update($data);

            return $task;
        }

        return $task;
    }
}

<?php

namespace App\Repositories;

interface CyclicTaskRepositoryInterface
{
    public function getCyclicTasks();

    public function deleteCyclicTask($taskId);

    public function deleteAllCyclicTasks();

    public function pauseCyclicTask($taskId);
}

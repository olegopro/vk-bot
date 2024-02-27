<?php

namespace App\Http\Controllers;

use App\Repositories\CyclicTaskRepositoryInterface;

final class CyclicTaskController extends Controller
{
    public function __construct(
        private readonly CyclicTaskRepositoryInterface $cyclicTaskRepository
    ) {}

    public function getCyclicTasks()
    {
        $cyclicTasks = $this->cyclicTaskRepository->getCyclicTasks();

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasks,
            'message' => 'Список циклических задач получен'
        ]);
    }

    public function deleteCyclicTask($taskId)
    {
        $cyclicTask = $this->cyclicTaskRepository->deleteCyclicTask($taskId);

        if (!$cyclicTask) {
            return response()->json([
                'success' => false,
                'error'   => 'Циклическая задача не найдена'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Циклическая задача удалена'
        ]);
    }

    public function deleteAllCyclicTasks()
    {
        $this->cyclicTaskRepository->deleteAllCyclicTasks();

        return response()->json([
            'success' => true,
            'message' => 'Все циклические задачи удалены'
        ]);
    }

    public function pauseCyclicTask($taskId)
    {
        $result = $this->cyclicTaskRepository->pauseCyclicTask($taskId);

        if (!$result) {
            return response()->json([
                'success' => false,
                'error'   => 'Циклическая задача не найдена'
            ]);
        }

        $message = $result['statusChangedTo'] === 'pause' ?
            'Циклическая задача поставлена на паузу' :
            'Циклическая задача возобновлена';

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}

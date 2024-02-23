<?php

namespace App\Http\Controllers;

use App\Repositories\CyclicTaskRepositoryInterface;

final class CyclicTaskController extends Controller
{
    public function __construct(
        private readonly CyclicTaskRepositoryInterface $cyclicTaskRepository
    ) {}

    public function getCyclicTasks() {
        $cyclicTasks = $this->cyclicTaskRepository->getCyclicTasks();

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasks,
            'message' => 'Список циклических задач получен'
        ]);
    }
}

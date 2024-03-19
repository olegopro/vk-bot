<?php

namespace App\Http\Controllers;

use App\Repositories\CyclicTaskRepositoryInterface;
use Illuminate\Http\Request;

final class CyclicTaskController extends Controller
{
    public function __construct(
        private readonly CyclicTaskRepositoryInterface $cyclicTaskRepository
    ) {}

    public function getCyclicTasks(Request $request)
    {
        // Получаем экземпляр пагинатора
        $cyclicTasksPaginator = $this->cyclicTaskRepository->getCyclicTasks($request->query('perPage', 30));

        // Извлекаем данные пагинации
        $pagination = collect($cyclicTasksPaginator->toArray())->except('data');

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasksPaginator->items(), // Извлекаем только массив данных
            'pagination' => $pagination, // Добавляем информацию о пагинации, если нужно
            'message' => 'Список циклических задач получен'
        ]);
    }

    public function editCyclicTask(Request $request, $taskId)
    {
        $data = $request->only(['account_id','total_task_count', 'tasks_per_hour', 'status', 'selected_times']);
        $cyclicTasks = $this->cyclicTaskRepository->editCyclicTask($taskId, $data);

        if (!$cyclicTasks) {
            return response()->json([
                'success' => false,
                'message' => 'Циклическая задача не найдена',
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => $cyclicTasks,
            'message' => 'Циклическая задача обновлена',
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

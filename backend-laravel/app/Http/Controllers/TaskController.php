<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class TaskController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $tasks = Task::all();

        return response($tasks);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function deleteAllTasks()
    {
        DB::table('tasks')->truncate(); // Очистка таблицы tasks
        DB::table('jobs')->truncate();  // Очистка таблицы jobs

        return response()->json(['message' => 'All tasks have been deleted']);
    }

    public function deleteTaskById($id)
    {
        // Получить статус задачи
        $taskStatus = DB::table('tasks')->where('id', $id)->value('status');

        // Если задача уже выполнена
        if ($taskStatus === 'done') {
            // Удаление задачи из таблицы tasks
            $this->deleteCompletedTask($id);
        }

        // Удаление задачи из очереди
        if ($taskStatus === 'pending') {
            $this->deletePendingTask($id);
        }
    }

    private function deletePendingTask(int $taskId)
    {
        DB::table('jobs')->orderBy('id')->chunk(100, function ($jobs) use ($taskId) {
            foreach ($jobs as $job) {
                // Декодирование поля payload
                $payload = json_decode($job->payload, true);

                // Десериализация поля command
                $command = unserialize($payload['data']['command']);

                // Получение идентификатора задачи из объекта команды
                $commandTaskId = $command->getTask()->id;

                // Если идентификатор задачи соответствует идентификатору задачи, который нужно удалить
                if ($commandTaskId === $taskId) {
                    // Удаление задачи из очереди
                    DB::table('jobs')->where('id', $job->id)->delete();

                    // Удаление задачи из таблицы tasks
                    DB::table('tasks')->where('id', $taskId)->delete();
                }
            }
        });

        return response()->json(['message' => 'Задача была удалена']);
    }

    private function deleteCompletedTask(int $taskId)
    {
        DB::table('tasks')->where('id', $taskId)->delete();

        return response()->json(['message' => 'Завершенная задача была удалена']);
    }
}

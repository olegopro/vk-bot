<?php

namespace App\Console\Commands;

use App\Http\Controllers\TaskController;
use App\Models\CyclicTask;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class RunGetNewsfeedPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:collectNewsfeedPostsForLikeTask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run collectNewsfeedPostsForLikeTask method every minute for active tasks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $activeTasks = CyclicTask::where('status', 'active')->get();

        foreach ($activeTasks as $task) {
            // Проверяем, есть ли у задачи доступные "лайки" для обработки
            if ($task->tasks_count > 0) {
                // Создание искусственного запроса
                $request = Request::create('', 'POST', [
                    'account_id' => $task->account_id,
                    'task_count' => 1,
                ]);

                // Вызов метода контроллера с искусственным запросом
                $response = app(TaskController::class)->collectNewsfeedPostsForLikeTask($request);

                // Уменьшаем количество лайков на 1 после успешного выполнения задачи
                $task->decrement('tasks_count');

                // Проверяем, достигло ли количество лайков 0 после уменьшения
                if ($task->tasks_count == 0) {
                    // Обновляем статус задачи на 'done'
                    $task->status = 'done';
                    $task->save();
                }
            }
        }
    }
}

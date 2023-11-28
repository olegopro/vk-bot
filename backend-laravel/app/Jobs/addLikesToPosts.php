<?php

namespace App\Jobs;

use App\Library\VkClient;
use App\Models\Task;
use App\Services\LoggingService;
use App\Services\LoggingServiceInterface;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class addLikesToPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $task;
    private $token;
    private $loggingService;
    private $screenName;

    /**
     * Create a new job instance.
     *
     * @param $task
     * @param $token
     * @param LoggingService $loggingService
     */
    public function __construct($task, $token, LoggingService $loggingService)
    {
        $this->task = $task;
        $this->token = $token;
        $this->loggingService = $loggingService;

        $this->screenName = DB::table('accounts')
                              ->where('access_token', $token)
                              ->value('screen_name');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $task = Task::find($this->task->id);

        // Проверка задачи на просроченность
        $deltaSeconds = 10; // разрешенная дельта в секундах

        if ($task->run_at && now()->diffInSeconds(new Carbon($task->run_at)) > $deltaSeconds) {
            $task->update([
                'status'        => 'canceled',
                'error_message' => "Просрочено: должна была запуститься в $task->run_at"
            ]);

            $this->loggingService->log(
                'account_task_likes',
                $this->screenName,
                "Просрочено: должна была запуститься в $task->run_at",
                ['task_id' => $this->task->id]
            );

            return;
        }

        // Обновление статуса задачи на 'active'
        $task->update(['status' => 'active']);

        // Выполнение основной логики задачи
        $response = (new VkClient($this->token))->request('likes.add', [
            'type'     => 'post',
            'owner_id' => $this->task->owner_id,
            'item_id'  => $this->task->item_id
        ]);

        $this->loggingService->log(
            'account_task_likes',
            $this->screenName,
            'VK API Response',
            ['response' => $response]
        );

        // Обновление статуса задачи на 'done'
        $task->update(['status' => 'done']);
    }

    public function failed(Exception $exception)
    {
        $this->loggingService->log(
            'account_task_likes',
            $this->screenName,
            'Failed Job Exception',
            ['exception' => $exception->getMessage()]
        );

        // Находим задачу и обновляем её статус и сообщение об ошибке
        $task = Task::find($this->task->id);

        $task?->update([
            'status'        => 'failed',
            'error_message' => $exception->getMessage()
        ]);
    }

    public function getTaskStatus() {
        return $this->task->status;
    }
}

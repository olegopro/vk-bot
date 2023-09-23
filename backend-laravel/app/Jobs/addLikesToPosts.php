<?php

namespace App\Jobs;

use App\Library\VkClient;
use App\Services\LoggingService;
use App\Services\LoggingServiceInterface;
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
        DB::table('tasks')
          ->where('id', '=', $this->task->id)
          ->update(['status' => 'active']);

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

        DB::table('tasks')
          ->where('id', '=', $this->task->id)
          ->update(['status' => 'done']);
    }

    public function failed(Exception $exception)
    {
        $this->loggingService->log(
            'account_task_likes',
            $this->screenName,
            'Failed Job Exception',
            ['exception' => $exception->getMessage()]
        );

        DB::table('tasks')
          ->where('id', '=', $this->task->id)
          ->update([
              'status'        => 'failed',
              'error_message' => $exception->getMessage()
          ]);
    }

    public function getTask()
    {
        return $this->task;
    }
}

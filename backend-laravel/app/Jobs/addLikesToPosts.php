<?php

namespace App\Jobs;

use App\Library\VkClient;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class addLikesToPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $task;
    private $token;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($task, $token)
    {
        $this->task = $task;
        $this->token = $token;
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

        if (response($response)) {
            DB::table('tasks')
              ->where('id', '=', $this->task->id)
              ->update(['status' => 'done']);
        } else {
            DB::table('tasks')
              ->where('id', '=', $this->task->id)
              ->update(['status' => 'cancelled']);
        }
    }
}

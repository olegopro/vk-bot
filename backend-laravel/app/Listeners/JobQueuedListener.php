<?php

namespace App\Listeners;

use App\Models\Task;
use Illuminate\Queue\Events\JobQueued;

class JobQueuedListener
{
    /**
     * Handle the event.
     *
     * @param JobQueued $event
     * @return void
     */
    public function handle(JobQueued $event)
    {
        $jobId = $event->id;
        $taskId = $event->job->getTaskId();

        Task::where('id', '=', $taskId)->update(['job_id' => $jobId]);
    }
}

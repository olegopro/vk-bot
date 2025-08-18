<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Models\Task;
use Illuminate\Queue\Events\JobQueued;


/**
 * Слушатель события постановки задачи в очередь.
 *
 * Этот класс реагирует на событие постановки задачи в очередь и обновляет соответствующую запись в базе данных,
 * сохраняя идентификатор задачи очереди для последующего использования.
 */
class JobQueuedListener
{
    /**
     * Обрабатывает событие постановки задачи в очередь.
     *
     * Этот метод вызывается, когда задача была успешно поставлена в очередь. Он извлекает идентификатор задачи
     * и обновляет соответствующую запись в базе данных, устанавливая идентификатор задачи очереди.
     *
     * @param JobQueued $event Событие, содержащее информацию о задаче, поставленной в очередь.
     * @return void
     */
    public function handle(JobQueued $event)
    {
        $jobId = $event->id;
        $taskId = $event->job->getTaskId();

        Task::where('id', '=', $taskId)->update(['job_id' => $jobId]);
    }
}

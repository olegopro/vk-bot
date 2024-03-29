<?php

namespace App\Console\Commands;

use App\Http\Controllers\TaskController;
use App\Models\CyclicTask;
use ATehnix\VkClient\Exceptions\VkException;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DispatchLikeToNewsfeedPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:DispatchLikeToNewsfeedPost';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run DispatchLikeToNewsfeedPost method every minute for active tasks';

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
     * Выполняет основную логику команды: обрабатывает активные циклические задачи на лайки,
     * обновляет расписание лайков в начале каждого часа и выполняет задачи в соответствии с расписанием.
     *
     * Этот метод выполняет несколько ключевых шагов:
     * 1. Получает все активные циклические задачи из базы данных.
     * 2. Проверяет, наступил ли новый час, чтобы обновить расписание лайков (`likes_distribution`) для каждой задачи.
     *    - Если да, генерирует новый набор уникальных случайных минут в пределах часа в соответствии с полем `tasks_per_hour`.
     *    - Сохраняет новое расписание в базе данных.
     * 3. Для каждой активной задачи проверяет, входит ли текущая минута в расписание лайков.
     *    - Если да и количество оставшихся задач (`tasks_count`) больше 0, выполняет задачу на постановку лайка.
     *    - Декрементирует `tasks_count` и инкрементирует `likes_count_hourly`.
     *    - Если после выполнения задачи `tasks_count` становится равным 0, обновляет статус задачи на 'done'.
     * 4. Сохраняет изменения в базе данных.
     *
     * @return void
     * @throws VkException
     */
    public function handle()
    {
        $activeTasks = CyclicTask::where('status', 'active')->get();
        $currentDay = now()->shortDayName; // Получаем текущий день Mon, Tue и т.д.
        $currentHour = now()->hour; // Получаем текущий час
        $currentMinute = now()->minute; // Получаем текущую минуту

        $dayMap = [
            'Mon' => 'пн',
            'Tue' => 'вт',
            'Wed' => 'ср',
            'Thu' => 'чт',
            'Fri' => 'пт',
            'Sat' => 'сб',
            'Sun' => 'вс',
        ];

        // Преобразование в двухбуквенное сокращение на русском
        $currentDayRu = $dayMap[$currentDay];

        $filteredTasks = $activeTasks->filter(function($task) use ($currentDayRu, $currentHour) {
            // Если нет расписания, задача пропускается
            if (is_null($task->selected_times)) return false;

            $selectedTimes = $task->selected_times;

            return isset($selectedTimes[$currentDayRu]) && $selectedTimes[$currentDayRu][$currentHour];
        });

        // Теперь $task - это только те задачи, которые должны выполняться в текущий час согласно расписанию selectedTimes
        foreach ($filteredTasks as $task) {
            // Проверяем, начался ли новый час
            if ($currentMinute === 0 && $task->updated_at->hour !== $currentHour) {
                // Обновляем `likes_distribution` - генерируем новую последовательность минут для выполнения задач за час
                $newLikesDistribution = app(TaskController::class)->generateUniqueRandomMinutes($task->tasks_per_hour);
                $task->likes_distribution = json_encode($newLikesDistribution);
                $task->save();
            }

            // Декодируем расписание лайков из JSON-строки в массив
            $likesDistribution = json_decode($task->likes_distribution, true);

            // Проверяем, входит ли текущая минута в расписание лайков
            if (in_array($currentMinute, $likesDistribution) && $task->remaining_tasks_count > 0) {
                // Создание искусственного запроса
                $request = Request::create('', 'POST', [
                    'account_id' => $task->account_id,
                    'task_count' => 1,
                ]);

                // Вызов метода контроллера с искусственным запросом
                $response = app(TaskController::class)->collectNewsfeedPostsForLikeTask($request, true);

                // Проверка успешности выполнения задачи
                if ($response->isSuccessful()) {
                    $task->decrement('remaining_tasks_count'); // Уменьшаем оставшееся количество задач

                    // Проверяем, достигло ли количество лайков 0 после уменьшения
                    if ($task->remaining_tasks_count == 0) {
                        // Обновляем статус задачи на 'done'
                        $task->status = 'done';
                    }

                    // Сохраняем изменения в задаче
                    $task->save();
                }
            }
        }
    }
}

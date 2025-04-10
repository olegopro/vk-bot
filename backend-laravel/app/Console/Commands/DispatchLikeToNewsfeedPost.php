<?php

namespace App\Console\Commands;

use App\Http\Controllers\TaskController;
use App\Models\CyclicTask;
use ATehnix\VkClient\Exceptions\VkException;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Класс DispatchLikeToNewsfeedPost - консольная команда для автоматического выполнения
 * циклических задач по постановке лайков на посты в ленте новостей ВКонтакте.
 *
 * Эта команда запускается планировщиком Laravel каждую минуту и является ключевым компонентом
 * для реализации циклических задач по автоматической постановке лайков на посты в VK.
 */
class DispatchLikeToNewsfeedPost extends Command
{
    /**
     * Сигнатура консольной команды.
     * Используется при вызове команды в консоли: php artisan run:DispatchLikeToNewsfeedPost
     *
     * @var string
     */
    protected $signature = 'run:DispatchLikeToNewsfeedPost';

    /**
     * Описание команды, объясняющее её назначение.
     *
     * @var string
     */
    protected $description = 'Run DispatchLikeToNewsfeedPost method every minute for active tasks';

    /**
     * Конструктор класса.
     * Вызывает конструктор родительского класса Command.
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
        // Получаем все активные циклические задачи из базы данных
        $activeTasks = CyclicTask::where('status', 'active')->get();

        // Получаем текущий день недели, час и минуту
        $currentDay = now()->shortDayName;  // Получаем текущий день Mon, Tue и т.д.
        $currentHour = now()->hour;         // Получаем текущий час (от 0 до 23)
        $currentMinute = now()->minute;     // Получаем текущую минуту (от 0 до 59)

        // Карта для конвертации английских сокращений дней недели в русские
        $dayMap = [
            'Mon' => 'пн',
            'Tue' => 'вт',
            'Wed' => 'ср',
            'Thu' => 'чт',
            'Fri' => 'пт',
            'Sat' => 'сб',
            'Sun' => 'вс',
        ];

        // Преобразование английского сокращения дня недели в русское двухбуквенное
        $currentDayRu = $dayMap[$currentDay];

        // Фильтрация задач: оставляем только те, которые должны выполняться в текущий день и час
        // согласно расписанию в поле selected_times
        $filteredTasks = $activeTasks->filter(function($task) use ($currentDayRu, $currentHour) {
            // Если у задачи нет расписания, она пропускается
            if (is_null($task->selected_times)) return false;

            // Получаем расписание задачи в формате массива, где ключи - дни недели, значения - массивы часов
            // selected_times = {
            //   "пн": [true, true, ...], // 24 значения для каждого часа
            //   "вт": [false, false, ...],
            //   ...
            // }
            $selectedTimes = $task->selected_times;

            // Проверяем, существует ли текущий день в расписании и активен ли текущий час
            // Если оба условия выполняются, задача будет обработана
            return isset($selectedTimes[$currentDayRu]) && $selectedTimes[$currentDayRu][$currentHour];
        });

        // Перебираем отфильтрованные задачи, которые должны выполняться в текущий час согласно расписанию
        foreach ($filteredTasks as $task) {
            // Проверяем, начался ли новый час, сравнивая текущий час с часом последнего обновления задачи
            if ($currentMinute === 0 && $task->updated_at->hour !== $currentHour) {
                // Если да, генерируем новое расписание лайков для текущего часа
                // Количество минут в расписании соответствует числу задач, которые должны быть выполнены за час
                $newLikesDistribution = app(TaskController::class)->generateUniqueRandomMinutes($task->tasks_per_hour);

                // Сохраняем новое расписание в задаче в виде JSON-строки
                $task->likes_distribution = json_encode($newLikesDistribution);
                $task->save();
            }

            // Декодируем JSON-строку расписания лайков в ассоциативный массив
            // likes_distribution содержит массив минут (от 1 до 60), когда нужно выполнять задачи
            $likesDistribution = json_decode($task->likes_distribution, true);

            // Проверяем, входит ли текущая минута в расписание лайков и есть ли оставшиеся задачи
            if (in_array($currentMinute, $likesDistribution) && $task->remaining_tasks_count > 0) {
                // Создаем искусственный HTTP-запрос для вызова метода контроллера
                // Этот запрос имитирует HTTP-запрос, который мог бы прийти от клиента
                $request = Request::create('', 'POST', [
                    'account_id' => $task->account_id, // ID аккаунта, от имени которого ставится лайк
                    'task_count' => 1, // Всегда выполняем только одну задачу за вызов
                ]);

                // Вызываем метод контроллера TaskController для выполнения задачи
                // Флаг true указывает, что это циклическая задача
                $response = app(TaskController::class)->createAndQueueLikeTasksFromNewsfeed($request, true);

                // Проверяем, был ли запрос успешным
                if ($response->isSuccessful()) {
                    // Уменьшаем счетчик оставшихся задач на 1
                    $task->decrement('remaining_tasks_count');

                    // Проверяем, достигло ли количество оставшихся задач 0
                    if ($task->remaining_tasks_count == 0) {
                        // Если да, обновляем статус задачи на 'done', что означает завершение всех запланированных лайков
                        $task->status = 'done';
                    }

                    // Сохраняем изменения в базе данных
                    $task->save();
                }
            }
        }
    }
}

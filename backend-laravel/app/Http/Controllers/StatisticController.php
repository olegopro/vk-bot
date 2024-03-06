<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticController extends Controller
{
    public function getStatistic()
    {
        $statistic = Task::where('status', 'done')
                         ->whereDate('created_at', '>', Carbon::now()->subDays(7))
                         ->get();

        // Тут может быть логика подготовки данных для Vue компонента

        return response()->json([
            'success' => true,
            'data'    => $statistic,
            'message' => 'Статистика за последние 7 дней получена'
        ]);
    }

    public function getWeeklyTaskStats()
    {
        Carbon::setLocale('ru'); // Устанавливаем русскую локаль для Carbon

        // Инициализируем массив с нулевыми значениями для каждого из последних 7 дней
        $daysOfWeek = collect(range(6, 0))->mapWithKeys(function ($day) {
            $date = Carbon::now()->subDays($day);
            // Используем mb_convert_case для изменения первой буквы на заглавную
            $dayOfWeek = mb_convert_case($date->isoFormat('dddd'), MB_CASE_TITLE, 'UTF-8');

            return [$dayOfWeek => 0]; // Используем день недели на русском в качестве ключа
        });

        // Получаем задачи за последние 7 дней со статусом 'done'
        $tasks = Task::where('status', 'done')
                     ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                     ->get()
                     ->groupBy(function ($task) {
                         // Группируем по дню недели на русском, делая первую букву заглавной
                         return mb_convert_case(Carbon::parse($task->created_at)->isoFormat('dddd'), MB_CASE_TITLE, 'UTF-8');
                     })
                     ->mapWithKeys(function ($tasks, $dayOfWeek) {
                         // Считаем количество задач в каждый день недели
                         return [$dayOfWeek => count($tasks)];
                     });

        // Преобразовываем задачи в массив и сохраняем порядок дней недели
        $tasksArray = $tasks->toArray();
        $finalData = [];

        // Сортируем дни недели в соответствии с порядком, начиная от самого раннего к последнему
        foreach ($daysOfWeek as $dayOfWeek => $value) {
            if (array_key_exists($dayOfWeek, $tasksArray)) {
                $finalData[$dayOfWeek] = $tasksArray[$dayOfWeek];
            } else {
                $finalData[$dayOfWeek] = 0; // Если в день недели не было задач, устанавливаем значение 0
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $finalData,
            'message' => 'Статистика за последние 7 дней получена'
        ]);
    }
}

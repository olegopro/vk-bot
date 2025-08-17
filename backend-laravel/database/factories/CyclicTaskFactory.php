<?php

namespace Database\Factories;

use App\Models\CyclicTask;
use Illuminate\Database\Eloquent\Factories\Factory;

class CyclicTaskFactory extends Factory
{
    protected $model = CyclicTask::class;

    public function definition()
    {
        // Генерация likes_distribution
        $likesDistribution = [];
        $minutesCount = $this->faker->numberBetween(1, 60); // Сколько минут будет в массиве
        for ($i = 0; $i < $minutesCount; $i++) {
            $likesDistribution[] = $this->faker->unique()->numberBetween(1, 60);
        }

        sort($likesDistribution); // Сортировка чисел от меньшего к большему
        $this->faker->unique($reset = true); // Сброс уникальности, чтобы избежать проблем при повторных вызовах

        // Генерация selected_times
        $daysOfWeek = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
        $selectedTimes = [];

        foreach ($daysOfWeek as $day) {
            $hours = array_fill(0, 24, false); // Инициализация массива из 24 элементов значениями false

            for ($i = 0; $i < 7; $i++) { // Допустим, случайно выбираем 7 часов в день как true
                $hours[$this->faker->numberBetween(0, 23)] = true;
            }

            $selectedTimes[$day] = $hours;
        }

        return [
            'account_id'            => $this->faker->numberBetween(1, 1000),
            'total_task_count'      => $this->faker->numberBetween(1, 100),
            'remaining_tasks_count' => $this->faker->numberBetween(1, 100),
            'tasks_per_hour'        => $this->faker->numberBetween(1, 24),
            'likes_distribution'    => json_encode($likesDistribution),
            'selected_times'        => $selectedTimes,
            'status'                => $this->faker->randomElement(['active', 'done', 'pause']),
            'started_at'            => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

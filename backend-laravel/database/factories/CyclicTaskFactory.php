<?php

namespace Database\Factories;

use App\Models\CyclicTask; // Убедитесь, что этот путь соответствует вашей реальной модели
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CyclicTaskFactory extends Factory
{
    protected $model = CyclicTask::class;

    public function definition()
    {
        return [
            'account_id' => $this->faker->numberBetween(1, 1000), // Предполагая, что у вас есть аккаунты с ID от 1 до 1000
            'total_task_count' => $this->faker->numberBetween(1, 100),
            'remaining_tasks_count' => $this->faker->numberBetween(1, 100),
            'tasks_per_hour' => $this->faker->numberBetween(1, 24),
            'likes_distribution' => $this->faker->randomElement(['uniform', 'random', 'peak', null]),
            'selected_times' => json_encode([$this->faker->time($format = 'H:i:s'), $this->faker->time($format = 'H:i:s')]),
            'status' => $this->faker->randomElement(['active', 'failed', 'canceled', 'done', 'pending', 'queued', 'pause']),
            'started_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}

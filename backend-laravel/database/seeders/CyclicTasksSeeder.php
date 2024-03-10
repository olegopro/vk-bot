<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CyclicTasksSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Предполагается, что уже существуют аккаунты, поэтому получаем их ID
        $accountIds = DB::table('accounts')->pluck('account_id');

        if ($accountIds->isEmpty()) {
            echo "Необходимо сначала создать аккаунты.\n";
            return;
        }

        foreach ($accountIds as $accountId) {
            DB::table('cyclic_tasks')->insert([
                'account_id' => $accountId,
                'total_task_count' => $faker->numberBetween(1, 100),
                'remaining_tasks_count' => $faker->numberBetween(1, 100),
                'tasks_per_hour' => $faker->numberBetween(1, 24),
                'likes_distribution' => $faker->randomElement(['uniform', 'peak', 'none']),
                'selected_times' => json_encode([$faker->time($format = 'H:i:s', $max = 'now')]),
                'status' => $faker->randomElement(['active, failed, canceled, done, pending, queued, pause']),
                'started_at' => $faker->dateTimeThisMonth()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}

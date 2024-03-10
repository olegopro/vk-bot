<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\CyclicTask;
use App\Models\Task;
use Illuminate\Database\Seeder;

class AccountsTasksSeeder extends Seeder
{
    public function run()
    {
        Account::factory(50)->create()->each(function ($account) {
            // Создаем обычные задачи
            Task::factory(random_int(5, 35))->create(['account_id' => $account->account_id]);

            // Создаем циклические задачи
            CyclicTask::factory(random_int(3, 10))->create(['account_id' => $account->account_id]);
        });
    }
}

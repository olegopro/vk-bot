<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Task;
use Illuminate\Database\Seeder;

class AccountsTasksSeeder extends Seeder
{
    public function run()
    {
        Account::factory(50)->create()->each(function ($account) {
            Task::factory(random_int(5, 100))->create(['account_id' => $account->account_id]);
        });
    }
}

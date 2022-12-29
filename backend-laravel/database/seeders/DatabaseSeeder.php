<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        Account::factory()
               ->count(10)
               ->create();

        Task::factory()
            ->count(100)
            ->create();
    }
}

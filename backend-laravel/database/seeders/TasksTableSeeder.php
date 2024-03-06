<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class TasksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 2000; $i++) {
            $timestamp = Carbon::now()->subDays(rand(0, 6))->subMinutes(rand(0, 1440));

            DB::table('tasks')->insert([
                'job_id' => rand(1, 1000),
                'account_id' => 9121607, // Нужно указывать id из таблицы Accounts
                'owner_id' => rand(1, 1000),
                'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'item_id' => rand(1, 20),
                'status' => 'done',
                'is_cyclic' => rand(0, 1),
                'run_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
        }
    }
}

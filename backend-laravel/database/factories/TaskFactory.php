<?php

namespace Database\Factories;

use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = Faker::create();
        $timestamp = Carbon::now()->subDays(rand(0, 6))->subMinutes(rand(0, 1440));

        return [
            'job_id'     => rand(1, 1000),
            'owner_id'   => rand(1, 1000),
            'first_name' => $faker->firstName,
            'last_name'  => $faker->lastName,
            'item_id'    => rand(1, 20),
            'status'     => $faker->randomElement(['failed', 'queued', 'done']),
            'is_cyclic'  => rand(0, 1),
            'run_at'     => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ];
    }
}

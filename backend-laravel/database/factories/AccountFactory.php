<?php

namespace Database\Factories;

use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = Faker::create();

        return [
            'account_id'    => $faker->unique()->randomNumber(5),
            'access_token'  => $faker->sha256,
            'screen_name'   => $faker->userName,
            'first_name'    => $faker->firstName,
            'last_name'     => $faker->lastName,
            'birthday_date' => $faker->date($format = 'Y-m-d', $max = 'now'),
        ];
    }
}

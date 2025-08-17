<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AccountsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (range(1, 50) as $index) {
            DB::table('accounts')->insert([
                'account_id'    => $faker->unique()->randomNumber(5),
                'access_token'  => $faker->sha256,
                'screen_name'   => $faker->userName,
                'first_name'    => $faker->firstName,
                'last_name'     => $faker->lastName,
                'birthday_date' => $faker->date($format = 'Y-m-d', $max = 'now'),
            ]);
        }
    }
}

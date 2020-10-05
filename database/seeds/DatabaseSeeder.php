<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(MissionsTableSeeder::class);
        $this->call(RewardsTableSeeder::class);
        $this->call(KeyPoolTableSeeder::class);
    }
}

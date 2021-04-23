<?php

use Illuminate\Database\Seeder;
use App\Utils\TestData;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);

        TestData::saveToDataBase();
    }
}

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

        // LLenar datos iniciales, solo si las tablas están en cero.
        if (\DB::table('ciudades')->count() === 0) {
            TestData::saveToDataBase();
        }

        // Actualizar contraseñas de usuarios para pruebas.
        if (env('APP_ENV') === 'local') {
            \DB::table('users')->update([
                'password' => \Hash::make(env('TEST_PASSWORD', \Str::random(18)))
            ]);
        }
    }
}

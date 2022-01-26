<?php

use App\Utils\TestData;
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
        // $this->call(UsersTableSeeder::class);

        // LLenar datos iniciales, solo si las tablas están en cero.
        if (\DB::table('ciudades')->count() === 0) {
            TestData::saveToDataBase();
        }

        // Actualizar contraseñas de usuarios para entorno de pruebas.
        if (env('APP_ENV') != 'production') {
            \DB::table('users')->update([
                'password' => \Hash::make(env('TEST_PASSWORD', 'secret')),
            ]);
        }
    }
}

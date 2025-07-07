<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        $this->call(RolesSeeder::class);

        User::factory()->create([

            'username' => 'admin_hj',
            'password' => bcrypt('123456789'),
            'cedulaidentidad' => '12345678',
            'nombres' => 'Admin HJ',
            'apellido_materno' => 'Admin',
            'apellido_paterno' => 'HJ',
            'celular' => '123456789',
            'email' => 'test@gmail.com',
            'direccion' => 'Av. Siempre Viva 123',
        ])->assignRole('administrador');
    }
}

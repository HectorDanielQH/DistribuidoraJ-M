<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rol_admin = Role::create(['name' => 'administrador']);
        $rol_vendedor = Role::create(['name' => 'vendedor']);

        $permisos_admin = Permission::create(['name' => 'administrador.permisos']);
        $permisos_vendedor = Permission::create(['name' => 'vendedor.permisos']);

        $permisos_admin->assignRole($rol_admin);
        $permisos_vendedor->assignRole($rol_vendedor);
    }
}

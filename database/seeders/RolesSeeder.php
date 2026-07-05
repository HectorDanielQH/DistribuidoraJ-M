<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rol_admin = Role::firstOrCreate(['name' => 'administrador']);
        $rol_vendedor = Role::firstOrCreate(['name' => 'vendedor']);
        $rol_contador = Role::firstOrCreate(['name' => 'contador']);
        $rol_mayorista = Role::firstOrCreate(['name' => 'mayorista']);

        $permisos_admin = Permission::firstOrCreate(['name' => 'administrador.permisos']);
        $permisos_vendedor = Permission::firstOrCreate(['name' => 'vendedor.permisos']);
        $permisos_contador = Permission::firstOrCreate(['name' => 'contador.permisos']);
        $permisos_mayorista = Permission::firstOrCreate(['name' => 'mayorista.permisos']);
        $permisos_productos_imagenes = Permission::firstOrCreate(['name' => 'productos.imagenes']);
        $permisos_productos_catalogo = Permission::firstOrCreate(['name' => 'productos.catalogo']);

        $permisos_admin->assignRole($rol_admin);
        $permisos_vendedor->assignRole($rol_vendedor);
        $permisos_contador->assignRole($rol_contador);
        $permisos_mayorista->assignRole($rol_mayorista);
        $permisos_productos_imagenes->assignRole($rol_admin);
        $permisos_productos_imagenes->assignRole($rol_vendedor);
        $permisos_productos_imagenes->assignRole($rol_mayorista);
        $permisos_productos_catalogo->assignRole($rol_admin);
        $permisos_productos_catalogo->assignRole($rol_vendedor);
        $permisos_productos_catalogo->assignRole($rol_mayorista);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

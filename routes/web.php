<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::middleware(['auth','verificar.estado'])->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('usuarios/imagenperfil/{id}', [UsuarioController::class, 'imagenPerfil'])->name('usuarios.imagenperfil');

    Route::resource('usuarios', UsuarioController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('lineas', LineaController::class);
    Route::resource('productos', ProductoController::class);
});
<?php

use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\FormaVentaController;
use App\Http\Controllers\LineaController;
use App\Http\Controllers\MarcaController;
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
    Route::post('marcas/mover/{id}', [MarcaController::class, 'mover'])->name('marca.mover');
    Route::get('productos/obtener-codigo', [ProductoController::class, 'obtenerCodigo'])->name('productos.autogenerar_codigo');
    Route::get('productos/imagenproducto/{id}', [ProductoController::class, 'imagenProducto'])->name('productos.imagen');
    Route::put('productos/actualizarCantidadProducto/{id}', [ProductoController::class, 'actualizarCantidadProducto'])->name('productos.updateCantidadStock');
    Route::put('productos/agregarPromocion/{id}',[ProductoController::class, 'agregarPromocion'])->name('productos.agregarPromocion'); 
    Route::put('productos/editarFotografia/{id}',[ProductoController::class, 'editarFotografia'])->name('productos.editarFotografia');
    Route::put('productos/editarCodigoManual/{id}',[ProductoController::class, 'editarCodigoManual'])->name('productos.updateCodigo');
    Route::put('productos/editarCodigoAutogenerar/{id}',[ProductoController::class, 'editarCodigoAutogenerar'])->name('productos.autogenerarCodigo');
    Route::put('productos/editarNombre/{id}',[ProductoController::class, 'editarNombre'])->name('productos.updateNombre');
    Route::put('productos/editarProveedorMarcaLinea/{id}',[ProductoController::class, 'editarProveedorMarcaLinea'])->name('productos.updateProveedor');
    Route::put('productos/editarDescripcion/{id}',[ProductoController::class, 'updateDescripcion'])->name('productos.updateDescripcion');
    Route::put('productos/editarPrecioCompraProducto/{id}',[ProductoController::class, 'updatePrecioCompraProducto'])->name('productos.updatePrecioCompraProducto');
    Route::put('productos/editarPrecioDesccripcionProducto/{id}',[ProductoController::class, 'updatePrecioDescripcionProducto'])->name('productos.updatePrecioDescripcionProducto');
    Route::put('productos/editarPresentacionProducto/{id}',[ProductoController::class, 'updatePresentacionProducto'])->name('productos.updatePresentacionProducto');

    
    Route::delete('productos/eliminarPromocion/{id}',[ProductoController::class, 'eliminarPromocion'])->name('productos.eliminarPromocion');
    Route::put('productos/editarPromocion/{id}',[ProductoController::class, 'editarPromocion'])->name('productos.editarPromocion');
    Route::put('formasventas/editarVisualizacion/{id}', [FormaVentaController::class, 'editarVisualizacion'])->name('formaventas.updateVisualizacion');

    Route::get('productos/obtener-productos-bajo-stock', [ProductoController::class, 'obtenerProductosBajoStock'])->name('productos.bajostock');
    Route::put('productos/darBajaoAlta/{id}', [ProductoController::class, 'darBaja'])->name('productos.dardealtaobaja');

    Route::get('clientesnoasignadosavendedores', [AsignacionController::class, 'clientesNoAsignadosAVendedores'])->name('asignacionclientes.getClientesNoAsignados');
    Route::get('clientesasignadosavendedores/{id}', [AsignacionController::class, 'clientesAsignadosAVendedores'])->name('asignacionclientes.getClientesAsignados');
    Route::delete('clientesasignadosavendedoreseliminar/{id_cliente}/{id_vendedor}', [AsignacionController::class, 'clientesAsignadosAVendedoresEliminar'])->name('asignacionclientes.destroyasignacion');
    Route::get('asignacionclientes/obtener-vendedores-ruta/{id_vendedor}', [AsignacionController::class, 'obtenerVendedoresRuta'])->name('vendedores.obtenerRuta');
    Route::put('asignacionclientes/resetear-vendedores-ruta/{id_vendedor}', [AsignacionController::class, 'resetearVendedoresRuta'])->name('vendedores.resetearRuta');

    Route::resource('usuarios', UsuarioController::class);
    Route::resource('proveedores', ProveedorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('lineas', LineaController::class);
    Route::resource('productos', ProductoController::class);
    Route::resource('marcas', MarcaController::class);
    Route::resource('formaventas', FormaVentaController::class);
    Route::resource('asignacionclientes', AsignacionController::class);
});
<?php

use App\Http\Controllers\Administrador\PermisosController;
use App\Http\Controllers\Administrador\ProveedorController;
use App\Http\Controllers\Administrador\LineaController;
use App\Http\Controllers\Administrador\MarcaController;
use App\Http\Controllers\Administrador\ProductoController;
use App\Http\Controllers\Administrador\LotesController;
use App\Http\Controllers\Administrador\UsuarioController;
use App\Http\Controllers\Administrador\FormaVentaController;
use App\Http\Controllers\Administrador\RutasController;
use App\Http\Controllers\Administrador\ClienteController;
use App\Http\Controllers\Administrador\AsignacionController;
use App\Http\Controllers\Administrador\ControlRutasController;
use App\Http\Controllers\Administrador\NoAtendidosController;
//---------------------------------------------------------------
use App\Http\Controllers\PreVentista\ProductoVendedorController;

use App\Http\Controllers\AsignacionVendedorController;
use App\Http\Controllers\PedidoAdministradorController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\RendimientoPersonalController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false]);

Route::middleware(['auth','verificar.estado'])->group(function () {

    //---Rutas para todos los usuarios autenticados---
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('usuarios/imagenperfil/{id}', [UsuarioController::class, 'imagenPerfil'])->name('usuarios.imagenperfil');
    //--Rutas Productos para todos los usuarios autenticados---
    Route::get('productos/imagenproducto/{id}', [ProductoController::class, 'imagenProducto'])->name('productos.imagen');
    Route::get('productos/obtener-codigo', [ProductoController::class, 'obtenerCodigo'])->name('productos.autogenerar_codigo');
    Route::get('productos/imagenproductocodigo/{codigo}', [ProductoController::class, 'imagenProductoCodigo'])->name('productos.imagen.codigo');
    //pdf descargar catalogo --VENDEDOR --ADMINISTRADOR
    Route::get('productos/vendedor/descargar-catalogo', [ProductoVendedorController::class, 'descargarCatalogo'])->name('productos.vendedor.descargarCatalogo');


    Route::prefix('administrador')->name('administrador.')->group(function () {
        //Rutas de permisos
        Route::get('/permisos', [PermisosController::class, 'index'])->name('permisos.index');
        Route::post('/permisos/store', [PermisosController::class, 'store'])->name('permisos.store');
        Route::delete('permisos/eliminar/{id}', [PermisosController::class, 'destroy'])->name('permisos.destroy');
        Route::put('permisos/editar/{id}', [PermisosController::class, 'update'])->name('permisos.update');

        //Rutas de Marcas para Proveedores
        Route::post('marcas/mover/{id}', [MarcaController::class, 'mover'])->name('marca.mover');

        //Rutas de formas de venta
        Route::get('formaventas/obtener-formas-venta/{id_producto}', [FormaVentaController::class, 'index'])->name('formaventas.index');
        Route::post('formaventas/store', [FormaVentaController::class, 'store'])->name('formaventas.store');
        Route::put('formaventas/editar/{id}', [FormaVentaController::class, 'update'])->name('formaventas.update');
        Route::delete('formaventas/eliminar/{id}', [FormaVentaController::class, 'destroy'])->name('formaventas.destroy');
        Route::put('formaventas/editar-visualizacion/{id}', [FormaVentaController::class, 'editarVisualizacion'])->name('formaventas.updateVisualizacion');
        Route::put('productos/editarPromocion/{id}',[ProductoController::class, 'editarPromocion'])->name('productos.editarPromocion');
        Route::put('productos/editarStock/{id}',[FormaVentaController::class, 'editarStock'])->name('productos.editarStock');

        
        //Rutas de Promocion
        Route::put('productos/agregarPromocion/{id}',[ProductoController::class, 'agregarPromocion'])->name('productos.agregarPromocion'); 
        Route::delete('productos/eliminarPromocion/{id}',[ProductoController::class, 'eliminarPromocion'])->name('productos.eliminarPromocion');

        //Rutas de actualizacion de datos de productos
        Route::put('productos/editarFotografia/{id}',[ProductoController::class, 'editarFotografia'])->name('productos.editarFotografia');
        Route::put('productos/editarCodigoManual/{id}',[ProductoController::class, 'editarCodigoManual'])->name('productos.updateCodigo');
        Route::put('productos/editarCodigoAutogenerar/{id}',[ProductoController::class, 'editarCodigoAutogenerar'])->name('productos.autogenerarCodigo');
        Route::put('productos/editarNombre/{id}',[ProductoController::class, 'editarNombre'])->name('productos.updateNombre');
        Route::put('productos/editarProveedorMarcaLinea/{id}',[ProductoController::class, 'editarProveedorMarcaLinea'])->name('productos.updateProveedor');
        Route::put('productos/editarDescripcion/{id}',[ProductoController::class, 'updateDescripcion'])->name('productos.updateDescripcion');
        Route::put('productos/editarPrecioCompraProducto/{id}',[ProductoController::class, 'updatePrecioCompraProducto'])->name('productos.updatePrecioCompraProducto');
        Route::put('productos/actualizarCantidadProducto/{id}', [ProductoController::class, 'actualizarCantidadProducto'])->name('productos.updateCantidadStock');
        Route::put('productos/editarPrecioDesccripcionProducto/{id}',[ProductoController::class, 'updatePrecioDescripcionProducto'])->name('productos.updatePrecioDescripcionProducto');
        Route::put('productos/editarPresentacionProducto/{id}',[ProductoController::class, 'updatePresentacionProducto'])->name('productos.updatePresentacionProducto');
        Route::put('productos/darBajaoAlta/{id}', [ProductoController::class, 'darBaja'])->name('productos.dardealtaobaja');

        //Ruta de informacion de productos de bajo stock
        Route::get('productos/obtener-productos-bajo-stock', [ProductoController::class, 'obtenerProductosBajoStock'])->name('productos.bajostock');

        //Rutas para subir clientes desde archivo
        Route::post('clientes/importar', [ClienteController::class, 'importarClientes'])->name('clientes.importar');

        //Rutas para ver asignaciones
        Route::get('clientesasignadosavendedores/{id}', [AsignacionController::class, 'clientesAsignadosAVendedores'])->name('asignacionclientes.getClientesAsignados');
        Route::get('rutas/asignadosavendedores/{id}', [AsignacionController::class, 'rutasAsignadasAVendedores'])->name('asignacionclientes.getRutasAsignadas');
        Route::delete('rutasasignadosavendedoreseliminar/{id_ruta}', [AsignacionController::class, 'rutasAsignadasAVendedoresEliminar'])->name('asignacionrutas.destroyasignacion');
        Route::post('asignacionclientes/registrar', [AsignacionController::class, 'clientesUnitarios'])->name('asignacionclientes.storeUnitario');

        //Rutas de control de rutas
        Route::get('controlrutas', [ControlRutasController::class, 'index'])->name('controlrutas.index');
        Route::get('controlrutas/{id}', [ControlRutasController::class, 'indexPreventista'])->name('controlrutas.preventista');
        Route::post('controlrutas/cerrar-asignaciones', [ControlRutasController::class, 'cerrarAsignaciones'])->name('controlrutas.cerrarAsignaciones');

        //rutas de busqueda de clientes
        Route::get('clientes/buscar', [ClienteController::class, 'buscarClientes'])->name('clientes.buscar');

        //Ruta pdf de clientes no atendidos
        Route::get('noatendidos/pdf', [NoAtendidosController::class, 'pdfNoAtendidos'])->name('noatendidos.pdf');

        //Rutas de clientes no atendidos
        Route::post('noatendidos/subsanar-observaciones', [NoAtendidosController::class, 'subsanarObservaciones'])->name('noatendidos.subsanadas');

        //Lotes de productos
        Route::get('lotes/obtenerProducto', [LotesController::class, 'obtenerProducto'])->name('lote.productos.buscarProducto');
        Route::get('lotes/obtenerDetalleProducto/{id}', [LotesController::class, 'obtenerDetalleProducto'])->name('lote.productos.detalleProducto');

        //Rutas generales de administrador
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('proveedores', ProveedorController::class);
        Route::resource('marcas', MarcaController::class);
        Route::resource('lineas', LineaController::class);
        Route::resource('productos', ProductoController::class);
        Route::resource('lotes', LotesController::class);
        Route::resource('rutas', RutasController::class);
        Route::resource('clientes', ClienteController::class);
        Route::resource('asignacionclientes', AsignacionController::class);
    });
    //---------------------------------------

    Route::prefix('preventistas')->name('preventistas.')->group(function () {
        //Producto vendedor Controller
        Route::get('productos/vendedor/obtener-productos', [ProductoVendedorController::class, 'obtenerProductos'])->name('productos.vendedor.obtenerProductos');

        Route::get('productos/vendedor/ver-detalle-productos-promocion', [ProductoVendedorController::class, 'verDetalleProductosPromocion'])->name('productos.vendedor.verDetalleProductosPromocion');
        Route::get('productos/vendedor/ver-detalle-productos-formas-venta/{id}', [ProductoVendedorController::class, 'verDetalleFormaVenta'])->name('productos.vendedor.verFormasVenta');
        Route::get('productos/vendedor/ver-detalle-productos-promocion/{id}', [ProductoVendedorController::class, 'verDetallePromocion'])->name('productos.vendedor.verDetallePromocion');
    });
    Route::get('asignaciones/rutasnoasignadosavendedores', [AsignacionController::class, 'RutasNoAsignadosAVendedores'])->name('asignacionclientes.getRutasNoAsignados');

    Route::get('asignacionclientes/obtener-vendedores-ruta/{id_vendedor}', [AsignacionController::class, 'obtenerVendedoresRuta'])->name('vendedores.obtenerRuta');
    Route::put('asignacionclientes/resetear-vendedores-ruta/{id_vendedor}', [AsignacionController::class, 'resetearVendedoresRuta'])->name('vendedores.resetearRuta');

    //asignacion de vendedores
    Route::get('asignacionVendedores/actualizar', [AsignacionVendedorController::class, 'index'])->name('asignacionvendedor.index');

    Route::put('asignacionVendedores/registrar-atencion/{id}', [AsignacionVendedorController::class, 'registrarAtencion'])->name('registrarAtencion.sinpedido');

    //Crear pedidos desdee vendedor
    Route::get('pedidos/vendedor/crear/{id}', [PedidoController::class, 'crearPedido'])->name('pedidos.vendedor.crear');
    Route::get('pedidos/vendedor/obtenerproducto/{id}', [PedidoController::class, 'ObtenerProductoParaPedido'])->name('pedidos.vendedor.obtenerProducto');
    Route::get('pedidos/vendedor/obtenerformaventa/{id}', [PedidoController::class, 'ObtenerFormaVenta'])->name('pedidos.vendedor.obtenerformaventa');
    Route::post('pedidos/vendedor/registrarpedido', [PedidoController::class, 'registrarPedido'])->name('pedidos.vendedor.registrarPedido');

    //ver pedidos desde vendedor
    Route::get('pedidos/vendedor/obtener-pedidos-realizados/{id_cliente}', [AsignacionVendedorController::class, 'obtenerPedidosProceso'])->name('pedidos.vendedor.obtenerPedidosProceso');
    Route::get('pedidos/vendedor/obtener-pedidos-realizados-pendientes/{id_cliente}', [AsignacionVendedorController::class, 'obtenerPedidosPendientes'])->name('pedidos.vendedor.obtenerPedidosPendientes');

    //pdf vendedor
    Route::get('pedidos/vendedor/obtener-pdf-rutas', [PedidoController::class, 'obtenerPdfRutas'])->name('pedidos.vendedor.obtenerPdfRutas');

    //pedido administrador Controller
    Route::get('pedidos/administrador/visualizacion', [PedidoAdministradorController::class,'index'])->name('pedidos.administrador.visualizacion');
    Route::get('pedidos/administrador/visualizacion-despachados', [PedidoAdministradorController::class,'visualizacionDespachados'])->name('pedidos.administrador.visualizacionDespachados');
    Route::get('pedidos/administrador/visualizacion-para-despachado', [PedidoAdministradorController::class,'visualizacionParaDespachado'])->name('pedidos.administrador.visualizacionParaDespachado');
    
    Route::get('pedidos/administrador/visualizacion-pedido/{id}', [PedidoAdministradorController::class,'visualizacionPedido'])->name('pedidos.administrador.visualizacionPedido');
    Route::post('pedidos/administrador/despachar-pedidos', [PedidoAdministradorController::class,'despacharPedido'])->name('pedidos.administrador.despacharPedido');
    Route::get('pedidos/administrador/devolucion-pedidos', [PedidoAdministradorController::class,'devolucionPedido'])->name('pedidos.administrador.devolucionPedido');
    Route::get('pedidos/administrador/devolucion-pedidos-numero-pedido/{pedido}', [PedidoAdministradorController::class,'devolucionPedidoDevolucion'])->name('pedidos.administrador.devolucionPedidoDevolucion');

    Route::put('pedidos/administrador/devolucion-pedidos-numero-pedido/cantidad/{id}', [PedidoAdministradorController::class,'devolucionPedidoDevolucionCantidad'])->name('pedidos.administrador.devolucionPedidoDevolucion.cantidad');
    Route::get('pedidos/administrador/producto/select/{id}', [PedidoAdministradorController::class,'productoSelectFormasVentas'])->name('pedidos.administrador.producto.select.cantidad');
    Route::put('pedidos/administrador/producto/select/actualizar/{id}', [PedidoAdministradorController::class,'productoSelectActualizar'])->name('pedidos.administrador.producto.select.actualizar');
    Route::delete('pedidos/administrador/producto/eliminar-promocion/{id}', [PedidoAdministradorController::class,'productoEliminarPromocion'])->name('pedidos.administrador.producto.eliminar.promocion');
    Route::delete('pedidos/administrador/producto/eliminar-promocion/total/{id}', [PedidoAdministradorController::class,'productoEliminarPromocionTotal'])->name('pedidos.administrador.producto.eliminar.promocion.total');
    Route::post('pedidos/administrador/producto/contabilizar-pedidos-pendientes',[PedidoAdministradorController::class,'contabilizarPedidosPendientes'])->name('pedidos.administrador.contabilizarTodosLosPendientes');
    //pedido administrador pdf
    Route::get('pedidos/administrador/visualizacion-pdf-despachar', [PedidoAdministradorController::class,'visualizacionPdfDespachar'])->name('pedidos.administrador.visualizacionPdfDespachar');

    //rendimiento personal Controller
    Route::get('rendimientopersonal/obtener-rendimiento-personal/{id}', [RendimientoPersonalController::class, 'rendimientoPersonal'])->name('rendimientopersonal.obtenerRendimientoPersonal');
    //VENTAS----------------------------
    Route::get('ventas/obtener-ventas/fechas', [VentaController::class, 'obtenerVentas'])->name('ventas.obtenerVentas.porfechas');
    Route::get('ventas/administrador/visualizacion-pedido/{id}', [VentaController::class,'visualizacionPedido'])->name('ventas.administrador.visualizacionPedido');
    Route::get('ventas/administrador/ventas-producto', [VentaController::class,'resporteVentasProducto'])->name('ventas.administrador.ventasProductos');
    Route::get('ventas/obtener-rendimiento-producto/{id}', [VentaController::class, 'reporteVentaProductosId'])->name('rendimientopersonal.obtenerVentasProductos');


    //----------------------------
    Route::resource('pedidos', PedidoController::class);
    Route::resource('rendimientopersonal', RendimientoPersonalController::class);
    Route::resource('ventas', VentaController::class);
});
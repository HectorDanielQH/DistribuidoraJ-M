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
use App\Http\Controllers\PreVentista\VentasVendedorController;
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

        Route::get('productos/obtener-productos-por-marca/{id}', [FormaVentaController::class, 'show'])->name('productos.obtenerProductosPorMarca');


        Route::get('productos/obtener-formas-venta-productos/{id}', [FormaVentaController::class, 'mostrarFormasVenta'])->name('productos.mostrarFormasVenta');


        //Ruta de Forma Venta para el producto
        Route::put('productos/editarFormaVenta/{id}',[FormaVentaController::class, 'updatePresentacionProducto'])->name('formaventas.editarNombreFormaVenta');

        
        //Rutas de Promocion
        Route::put('productos/agregarPromocion/{id}',[ProductoController::class, 'agregarPromocion'])->name('productos.agregarPromocion'); 
        Route::delete('productos/eliminarPromocion/{id}',[ProductoController::class, 'eliminarPromocion'])->name('productos.eliminarPromocion');

        //Rutas de actualizacion de datos de productos
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
        Route::get('productos/obtener-codigo', [ProductoController::class, 'obtenerCodigo'])->name('productos.autogenerar_codigo');

        //Productos
        Route::put('productos/actualizar-cantidad/{id}', [ProductoController::class, 'actualizarCantidadProducto'])->name('productos.updateCantidadStock');
        Route::get('productos/obtener/nombres', [ProductoController::class, 'obtenerProductoPorNombre'])->name('productos.busquedaNombreProducto');
        Route::get('productos/obtener/id/{id}', [ProductoController::class, 'obtenerProductoPorId'])->name('productos.busquedaIdProducto');

        //----PRODUCTOS SECCION EDICION DE PEDIDOS DESDE ADMINISTRADOR----
        Route::get('productos/obtener-productos-para-edicion', [ProductoController::class, 'obtenerProductosParaEdicion'])->name('productos.obtenerProductosParaEdicion');

        //Pedido administrador Controller
        Route::get('pedidos/administrador/visualizacion', [PedidoAdministradorController::class,'index'])->name('pedidos.administrador.visualizacion');
        Route::get('pedidos/administrador/visualizacion/{id}/editar', [PedidoAdministradorController::class,'editarPedido'])->name('pedidos.administrador.editar');
        Route::put('pedidos/administrador/visualizacion/actualizar/{id}', [PedidoAdministradorController::class,'agregarProductoPedido'])->name('pedidos.administrador.agregarProducto');
        Route::delete('pedidos/administrador/visualizacion/eliminar-producto/{id}', [PedidoAdministradorController::class,'eliminarProductoPedido'])->name('pedidos.administrador.eliminarProducto');


        //----------------EDICION DE VENTAS POR PEDIDO----------------//
        Route::get('ventas/administrador/ventas-por-pedido', [VentaController::class,'ventasPorFechasContabilizadas'])->name('ventas.administrador.ventasPorPedido');
        Route::put('ventas/administrador/mover-fecha-arqueo/{fecha_arqueo}', [VentaController::class,'moverFechaArqueo'])->name('ventas.administrador.moverFechaArqueo');
        Route::get('ventas/administrador/ventas-por-pedido/{fecha_arqueo}', [VentaController::class,'visualizacionVentasPorFechaArqueo'])->name('ventas.administrador.visualizacionVentasPorFechaArqueo');


        Route::get('ventas/administrador/ventas-por-pedido/{fecha_arqueo}/ver', [VentaController::class,'verVentaPorFechaArqueo'])->name('ventas.administrador.verVentaPorFechaArqueo');
        //-----------------------------------------------------------------------

        //-----------------------PEDIDOS DESPACHADOS-----------------------//

        Route::get('pedidos/administrador/visualizacion/{id}/editar-despachados', [PedidoAdministradorController::class,'editarPedidoDespachado'])->name('pedidos.administrador.editar.despachados');
        Route::put('pedidos/administrador/visualizacion/actualizar-despachados/{id}', [PedidoAdministradorController::class,'agregarProductoPedidoDespachado'])->name('pedidos.administrador.agregarProducto.despachados');
        Route::delete('pedidos/administrador/visualizacion/eliminar-producto/despachados/{id}', [PedidoAdministradorController::class,'eliminarProductoPedidoDespachado'])->name('pedidos.administrador.eliminarProducto.despachados');

        //-----------------------------------------------------------------------

        //-----------------------PEDIDOS CONTABILIZADOS-----------------------//
        Route::get('pedidos/administrador/visualizacion-contabilizados', [PedidoAdministradorController::class,'visualizacionContabilizados'])->name('pedidos.administrador.visualizacionContabilizados');
        Route::get('pedidos/administrador/visualizacion/{id}/editar-contabilizados', [PedidoAdministradorController::class,'editarPedidoContabilizado'])->name('pedidos.administrador.editar.contabilizados');
        
        Route::put('pedidos/administrador/visualizacion/actualizar-contabilizado/{id}', [PedidoAdministradorController::class,'agregarProductoPedidoContabilizado'])->name('pedidos.administrador.agregarProducto.contabilizado');
        Route::delete('pedidos/administrador/visualizacion/eliminar-producto/contabilizado/{id}', [PedidoAdministradorController::class,'eliminarProductoPedidoContabilizado'])->name('pedidos.administrador.eliminarProducto.contabilizado');


        Route::put('pedidos/administrador/recontabilizar-pedido/{numero_pedido}', [PedidoAdministradorController::class,'recontabilizarPedido'])->name('pedidos.administrador.recontabilizarPedido');

        //--------------------------------------------------------------------//

        //---------------------------VENTAS--------------------------------//

        Route::get('ventas/administrador/crear-venta', [VentaController::class,'crearVenta'])->name('ventas.administrador.crearVenta');
        Route::post('ventas/administrador/guardar-venta', [VentaController::class,'guardarVenta'])->name('ventas.administrador.guardarVenta');

        //-----------------------------------------------------------------//

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
        
        //registrar pedido desde vendedor
        Route::get('pedido/vendedor/cliente/{id}', [PedidoController::class, 'crearPedido'])->name('registrar.pedido');


        //------------MIS VENTAS----------------
        Route::get('ventas/vendedor/mis-ventas', [VentasVendedorController::class, 'index'])->name('ventas.vendedor.misVentas');
        Route::get('ventas/vendedor/mis-ventas/detalle/{fecha_contabilizacion}', [VentasVendedorController::class, 'detalleVentasPorFechaContabilizacion'])->name('ventas.vendedor.detalleVentasPorFechaContabilizacion');
        Route::get('pedidos/vendedor/mi-numero-de-pedido/{numero_pedido}', [PedidoController::class, 'obtenerPedidosPorNumero'])->name('ventas.vendedor.miNumeroDePedido');
        //------------------------------------
    });
    Route::get('asignaciones/rutasnoasignadosavendedores', [AsignacionController::class, 'RutasNoAsignadosAVendedores'])->name('asignacionclientes.getRutasNoAsignados');

    Route::get('asignacionclientes/obtener-vendedores-ruta/{id_vendedor}', [AsignacionController::class, 'obtenerVendedoresRuta'])->name('vendedores.obtenerRuta');
    Route::put('asignacionclientes/resetear-vendedores-ruta/{id_vendedor}', [AsignacionController::class, 'resetearVendedoresRuta'])->name('vendedores.resetearRuta');

    //asignacion de vendedores
    Route::get('asignacionVendedores/actualizar', [AsignacionVendedorController::class, 'index'])->name('asignacionvendedor.index');

    Route::put('asignacionVendedores/registrar-atencion/{id}', [AsignacionVendedorController::class, 'registrarAtencion'])->name('registrarAtencion.sinpedido');

    //Crear pedidos desdee vendedor
    Route::get('pedidos/vendedor/obtenerproducto/{id}', [PedidoController::class, 'ObtenerProductoParaPedido'])->name('pedidos.vendedor.obtenerProducto');
    Route::get('pedidos/vendedor/obtenerformaventa/{id}', [PedidoController::class, 'ObtenerFormaVenta'])->name('pedidos.vendedor.obtenerformaventa');
    Route::post('pedidos/vendedor/registrarpedido', [PedidoController::class, 'registrarPedido'])->name('pedidos.vendedor.registrarPedido');

    //ver pedidos desde vendedor
    Route::get('pedidos/vendedor/obtener-pedidos-realizados/{id_cliente}', [AsignacionVendedorController::class, 'obtenerPedidosProceso'])->name('pedidos.vendedor.obtenerPedidosProceso');
    Route::get('pedidos/vendedor/obtener-pedidos-realizados-pendientes/{id_cliente}', [AsignacionVendedorController::class, 'obtenerPedidosPendientes'])->name('pedidos.vendedor.obtenerPedidosPendientes');

    //pdf vendedor
    Route::get('pedidos/vendedor/obtener-pdf-rutas', [PedidoController::class, 'obtenerPdfRutas'])->name('pedidos.vendedor.obtenerPdfRutas');

    //pedido administrador Controller--producto despachado
    Route::get('pedidos/administrador/visualizacion-despachados', [PedidoAdministradorController::class,'visualizacionDespachados'])->name('pedidos.administrador.visualizacionDespachados');
   
    

    Route::get('pedidos/administrador/visualizacion-para-despachado', [PedidoAdministradorController::class,'visualizacionParaDespachado'])->name('pedidos.administrador.visualizacionParaDespachado');
    
    Route::get('pedidos/administrador/visualizacion-pedido/{id}', [PedidoAdministradorController::class,'visualizacionPedido'])->name('pedidos.administrador.visualizacionPedido');
    Route::post('pedidos/administrador/despachar-pedidos', [PedidoAdministradorController::class,'despacharPedido'])->name('pedidos.administrador.despacharPedido');
    
    //--------------------------------DEVOLUCION DE PEDIDOS DESPACHADOS-----------------------------
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

    Route::get('pedidos/administrador/visualizacion-pdf-despachar-pendientes', [PedidoAdministradorController::class,'visualizacionPdfDespacharPendientes'])->name('pedidos.administrador.visualizacionPdfDespachar.pedidosPendientes');

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
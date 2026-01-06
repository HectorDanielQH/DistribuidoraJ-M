<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\Administrador\Mayorista;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class MayoristaController extends Controller
{
    public function index(Request $request)
    {
        if($request->ajax()){
            $data=Mayorista::query();
            return DataTables()::of($data)
                ->addColumn('codigo_producto', function($row){
                    return $row->producto->codigo_producto;
                })
                ->addColumn('acciones', function($row){
                    $btn = '<a href="'.route('administrador.mayoristas.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    return $btn;
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }
        return view('administrador.mayoristas.index');
    }

    public function crearProductoMayorista()
    {
        return view('administrador.mayoristas.create');
    }

    public function buscarProductoMayorista(Request $request)
    {
        // 1. Obtener el término de búsqueda
        $term = $request->input('palabra_clave'); // Asegúrate que coincida con el JS

        // 2. Construir la consulta
        $productos = Producto::select('id', 'codigo', 'nombre_producto','descripcion_producto','presentacion')
            ->where(function ($query) use ($term) {
                $query->where('codigo', 'ILIKE', '%' . $term . '%')
                    ->orWhere('nombre_producto', 'ILIKE', '%' . $term . '%');
            })
            ->limit(10)
            ->get();

        // 3. Select2 espera un array, si está vacío devuelve array vacío, no 404
        // Esto evita errores de consola en el navegador
        return response()->json($productos);
    }
}

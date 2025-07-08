<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class PermisosController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }

    public function index(Request $request, DataTables $dataTable)
    {
        if($request->ajax()){
            $roles = Role::query()
                ->select(['id', 'name', 'guard_name', 'created_at'])
                ->withCount('permissions')
                ->orderBy('created_at', 'desc');

            return $dataTable->eloquent($roles)
                ->addColumn('permissions', function ($role) {
                    return $role->permissions->pluck('name')->implode(', ');
                })
                ->addColumn('actions', function ($role) {
                    $btn = '<div class="btn-group d-flex justify-content-center align-items-center" role="group">';
                    $btn .= '<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editRoleModal" data-id="'.$role->id.'" onclick="editRole(this)"><i class="fas fa-edit"></i></button>';
                    $btn .= '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteRoleModal" data-id="'.$role->id.'" onclick="deleteRole(this)"><i class="fas fa-trash"></i></button>';
                    $btn .= '<button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPermiso" data-id="'.$role->id.'" onclick="addPermiso(this)"><i class="fas fa-plus"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('administrador.permisos.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        return response()->json(['success' => true, 'message' => 'Permiso creado exitosamente.', 'role' => $role]);
    }
}

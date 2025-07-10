<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
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

        $role = Role::create(['name' => $request->name]);
        $permissions = Permission::create(['name' => $request->name.'.permisos']);
        $permissions->assignRole($role);

        return response()->json(['success' => true, 'message' => 'Permiso creado exitosamente.', 'role' => $role]);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,'.$id,
        ]);

        $role = Role::findOrFail($id);

        // Update permissions
        $permissions = Permission::where('name', $role->name.'.permisos')->first();
        if ($permissions) {
            $permissions->name = $request->name.'.permisos';
            $permissions->save();
        }
        $role->name = $request->name;
        $role->save();

        return response()->json(['success' => true, 'message' => 'Permiso actualizado exitosamente.', 'role' => $role]);
    }

    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = $role->permissions;
        foreach ($permissions as $permission) {
            $permission->delete();
        }
        $role->permissions()->detach();
        $role->delete();

        return response()->json(['success' => true, 'message' => 'Permiso eliminado exitosamente.']);
    }
}

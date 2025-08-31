<?php

namespace App\Http\Controllers\Administrador;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UsuarioController extends Controller
{    
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('can:administrador.permisos');
    }
    public function index(Request $request, DataTables $dataTables)
    {
        if($request->ajax()){
            $query = User::query()->with('roles');


            if ($request->filled('nombres_completos')) {
                $nombre = strtoupper(trim($request->nombres_completos));
                $query->where(function($q) use ($nombre) {
                    $q->whereRaw('UPPER(nombres) LIKE ?', ["%{$nombre}%"])
                    ->orWhereRaw('UPPER(apellido_paterno) LIKE ?', ["%{$nombre}%"])
                    ->orWhereRaw('UPPER(apellido_materno) LIKE ?', ["%{$nombre}%"]);
                });
            }

            if ($request->filled('cedulaidentidad')) {
                $ci = strtoupper(trim($request->cedulaidentidad));
                $query->where('cedulaidentidad', 'like', "%{$ci}%");
            }
            
            return $dataTables->eloquent($query)
                ->addColumn('cedulaidentidad', function ($usuario) {
                    return $usuario->cedulaidentidad;
                })
                ->addColumn('foto_perfil', function ($usuario) {
                    if ($usuario->foto_perfil && Storage::disk('public')->exists($usuario->foto_perfil)) {
                        return '<img src="' . Storage::url($usuario->foto_perfil) . '" class="img-thumbnail" style="width: 50px; height: 50px;">';
                    }

                    return '<img src="' . asset('images/logo_profile.webp') . '" class="img-thumbnail" style="width: 50px; height: 50px;">';
                })
                ->addColumn('nombres_completos', function ($usuario) {
                    return trim(strtoupper($usuario->nombres . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno));
                })
                ->addColumn('celular', function ($usuario) {
                    return $usuario->celular;
                })
                ->addColumn('rol', function ($usuario) {
                    $rol = $usuario->getRoleNames()->first();
                    if ($rol) {
                        return '<span class="badge badge-success">' . e($rol) . '</span>';
                    }
                    return '<span class="badge badge-danger">No asignado</span>';
                })
                ->addColumn('action', function ($usuario) {
                    $editButton = '<button class="btn btn-warning btn-sm edit-user" id-usuario="' . $usuario->id . '" onclick="editarUsuario(this)" data-toggle="modal" data-target="#modalEditarUsuario"><i class="fas fa-edit"></i></button>';
                    $deleteButton = '<button class="btn btn-danger btn-sm delete-user" id-usuario="' . $usuario->id . '" onclick="eliminarUsuario(this)"><i class="fas fa-trash"></i></button>';
                    $viewButton = '<button class="btn btn-info btn-sm view-user" id-usuario="' . $usuario->id . '" onclick="visualizarUsuario(this)" data-toggle="modal" data-target="#modalVisualizar"><i class="fas fa-eye"></i></button>';
                    return $editButton . ' ' . $deleteButton . ' ' . $viewButton;
                })
                ->rawColumns(['foto_perfil','rol','action'])
                ->make(true);
        }
        $roles = Role::all();
        return view('administrador.usuarios.index_users', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cedulaidentidad' => 'required|string|max:255|unique:users,cedulaidentidad',
            'nombres' => 'required|string|max:255',
            'apellidopaterno' => 'nullable|string|max:255|required_without:apellidomaterno',
            'apellidomaterno' => 'nullable|string|max:255|required_without:apellidopaterno',
            'celular' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'direccion' => 'required|string|max:255',
            'estado' => 'required',
            'rol' => 'required|exists:roles,name',
            'fotoperfil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ],[
            'cedulaidentidad.required' => 'El campo Cédula de Identidad es obligatorio.',
            'cedulaidentidad.unique' => 'La Cédula de Identidad ya está registrada.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'apellidopaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'apellidomaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'email.required' => 'El campo Correo Electrónico es obligatorio.',
            'email.unique' => 'El Correo Electrónico ya está registrado.',
            'direccion.required' => 'El campo Dirección es obligatorio.',
            'estado.required' => 'El campo Estado es obligatorio.',
            'rol.required' => 'El campo Rol es obligatorio.',
            'rol.exists' => 'El Rol seleccionado no es válido.',
            'fotoperfil.image' => 'El archivo debe ser una imagen.',
            'fotoperfil.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg.',
            'fotoperfil.max' => 'La imagen no debe exceder los 2MB.',
        ]);

        if($request->hasFile('fotoperfil')) {
            
            $path = $request->file('fotoperfil')->store('foto_perfil', 'public');

            User::create([
                'username' => trim(strtoupper($request->cedulaidentidad)),
                'password' => bcrypt(trim(strtoupper($request->cedulaidentidad))),
                'cedulaidentidad' => trim(strtoupper($request->cedulaidentidad)),
                'nombres' => trim(strtoupper($request->nombres)),
                'apellido_paterno' => $request->apellidopaterno ? trim(strtoupper($request->apellidopaterno)):null,
                'apellido_materno' => $request->apellidomaterno ?trim(strtoupper($request->apellidomaterno)):null,
                'celular' => trim(strtoupper($request->celular)),
                'email' => trim(strtolower($request->email)),
                'direccion' => trim(strtoupper($request->direccion)),
                'estado' => trim(strtoupper($request->estado)),
                'foto_perfil' => $path,
            ])->assignRole($request->rol);

        } else {
            User::create([
                'username' => trim(strtoupper($request->cedulaidentidad)),
                'password' => bcrypt(trim(strtoupper($request->cedulaidentidad))),
                'cedulaidentidad' => trim(strtoupper($request->cedulaidentidad)),
                'nombres' => trim(strtoupper($request->nombres)),
                'apellido_paterno' => trim(strtoupper($request->apellidopaterno)),
                'apellido_materno' => trim(strtoupper($request->apellidomaterno)),
                'celular' => trim(strtoupper($request->celular)),
                'email' => trim(strtolower($request->email)),
                'direccion' => trim(strtoupper($request->direccion)),
                'estado' => trim(strtoupper($request->estado)),
                'foto_perfil' => null,
            ])->assignRole($request->rol);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado correctamente.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $usuario = User::findOrFail($id);
        $rol_usuario = $usuario->getRoleNames()->first();
        return response()->json([
            'status' => 'success',
            'usuario' => $usuario,
            'rol' => $rol_usuario,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'cedulaidentidad' => 'required|string|max:255|unique:users,cedulaidentidad,' . $id,
            'nombres' => 'required|string|max:255',
            'apellidopaterno' => 'nullable|string|max:255|required_without:apellidomaterno',
            'apellidomaterno' => 'nullable|string|max:255|required_without:apellidopaterno',
            'celular' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'direccion' => 'required|string|max:255',
            'estado' => 'required',
            'rol' => 'required|exists:roles,name',
            'fotoperfil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ],[
            'cedulaidentidad.required' => 'El campo Cédula de Identidad es obligatorio.',
            'cedulaidentidad.unique' => 'La Cédula de Identidad ya está registrada.',
            'nombres.required' => 'El campo Nombres es obligatorio.',
            'apellidopaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'apellidomaterno.required_without' => 'Debe ingresar al menos el Apellido Paterno o el Apellido Materno.',
            'celular.required' => 'El campo Celular es obligatorio.',
            'email.required' => 'El campo Correo Electrónico es obligatorio.',
            'email.unique' => 'El Correo Electrónico ya está registrado.',
            'direccion.required' => 'El campo Dirección es obligatorio.',
            'estado.required' => 'El campo Estado es obligatorio.',
            'rol.required' => 'El campo Rol es obligatorio.',
            'rol.exists' => 'El Rol seleccionado no es válido.',
            'fotoperfil.image' => 'El archivo debe ser una imagen.',
            'fotoperfil.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg.',
            'fotoperfil.max' => 'La imagen no debe exceder los 2MB.',
        ]);

        $usuario = User::findOrFail($id);

        if ($request->hasFile('fotoperfil')) {
            if ($usuario->foto_perfil && Storage::exists($usuario->foto_perfil)) {
                Storage::delete($usuario->foto_perfil);
            }
            $file = $request->file('fotoperfil');
            $path = $file->store('foto_perfil', 'public');
        } else {
            $path = $usuario->foto_perfil;
        }

        $usuario->update([
            'username' => trim(strtoupper($request->cedulaidentidad)),
            'cedulaidentidad' => trim(strtoupper($request->cedulaidentidad)),
            'nombres' => trim(strtoupper($request->nombres)),
            'apellido_paterno' => $request->apellidopaterno ? trim(strtoupper($request->apellidopaterno)) : null,
            'apellido_materno' => $request->apellidomaterno ? trim(strtoupper($request->apellidomaterno)) : null,
            'celular' => trim(strtoupper($request->celular)),
            'email' => trim(strtolower($request->email)),
            'direccion' => trim(strtoupper($request->direccion)),
            'estado' => trim(strtoupper($request->estado)),
            'password' => bcrypt(trim(strtoupper($request->cedulaidentidad))),
            'foto_perfil' => $path,
        ]);

        $usuario->syncRoles([$request->rol]);


        return response()->json([
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente.',
        ]);   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->foto_perfil && Storage::disk('local')->exists($usuario->foto_perfil)) {
            Storage::disk('local')->delete($usuario->foto_perfil);
        }

        $usuario->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }


    public function imagenPerfil(string $id)
    {
        $usuario = User::findOrFail($id);
        if (!$usuario->foto_perfil || !Storage::disk('local')->exists($usuario->foto_perfil)) {
            abort(404);
        }

        return response()->file(storage_path('app/private/' . $usuario->foto_perfil));
    }
}

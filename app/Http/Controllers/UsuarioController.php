<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('nombre')) {
            $keywords = explode(' ', $request->nombre);
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->where(function ($subquery) use ($word) {
                        $subquery->where('nombres', 'like', '%' . $word . '%')
                                ->orWhere('apellido_paterno', 'like', '%' . $word . '%')
                                ->orWhere('apellido_materno', 'like', '%' . $word . '%');
                    });
                }
            });
        }

        if ($request->filled('ci')) {
            $query->where('cedulaidentidad', 'like', '%' . $request->ci . '%');
        }

        $usuarios = $query->paginate(10);

        $roles = Role::all();

        return view('administrador.usuarios.index_users', compact('usuarios', 'request','roles'))->with('eliminar_busqueda', $request->filled('nombre') || $request->filled('ci'));
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
            
            $file = $request->file('fotoperfil');
            $path=$file->store('fotosperfil','local');

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
            $path = $file->store('fotosperfil', 'local');
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

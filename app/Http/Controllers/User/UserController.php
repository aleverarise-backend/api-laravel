<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use App\Mail\UserMailChanged;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{

    public function __construct(){
        $this->middleware('client.credentials')->only(['store', 'resend']);
        $this->middleware('auth:api')->except(['store', 'resend', 'verify']);
        $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);

        $this->middleware('scope:manage-account')->only(['show', 'update']);

        $this->middleware('can:view,user')->only('show');
        $this->middleware('can:update,user')->only('update');
        $this->middleware('can:delete,user')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->allowedAdminAction();
        
        $usuarios = User::all();
        // return response()->json(['data' => $usuarios], 200);
        return $this->showAll($usuarios);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $rules);

        $campos                       = $request->all();
        $campos['password']           = bcrypt($request->password);
        $campos['verified']           = User::usuario_no_verificado;
        $campos['verification_token'] = User::generarVerificacionToken();
        $campos['admin']              = User::usuario_regular;

        $usuario = User::create($campos);
        // return response()->json(['data' => $usuario], 201);
        return $this->showOne($usuario, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // $usuario = User::findOrFail($id);
        return $this->showOne($user, 201);
        // return response()->json(['data' => $usuario], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        
        $rules = [
            'email'    => 'email|unique:users,email, '. $user->id,
            'password' => 'min:6|confirmed',
            'admin'    => 'in:' . User::usuario_administrador . ',' . User::usuario_regular,
        ];

        $this->validate($request, $rules);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified           = User::usuario_no_verificado;
            $user->verification_token = User::generarVerificacionToken();
            $user->email              = $request->email;
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if ($request->has('admin')) {
            $this->allowedAdminAction();
            if(!$user->esVerificado()){
                // return response()->json(['Error' => 'Unicamente los usuarios verificados pueden cambiar su valor de administrador', 'code' => '409'], 409);
                return $this->errorResponse(['Error' => 'Unicamente los usuarios verificados pueden cambiar su valor de administrador'] , 409);
            }
            $user->admin = $request->admin;
        }

        if(!$user->isDirty()){
            return $this->errorResponse(['Error' => 'se debe especificar un valor diferente para actualizar'] , 409);
            // return response()->json(['Error' => 'se debe especificar un valor diferente para actualizar', 'code' => '409'], 409);
        }

        $user->save();

        // return response()->json(['data' => $user], 200);
        return $this->showOne($user, 201);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        // return response()->json(['data' => $user], 200);
        return $this->showOne($user, 201);
    }

    public function verify($token){
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->verified = User::usuario_verificado;
        $user->verification_token = null;
        $user->save();
        return $this->showMessage('la cuenta ha sido verificada', 202);
    }

    public function resend(User $user){
        if($user->verified == 1){
            return $this->errorResponse('Este usuario ya ha sido verificado', 409);
        }

        retry(5, function () use ($user){
            Mail::to($user)->send(new UserMailChanged($user));
        }, 100);


        return $this->showMessage('El correo de verificacion ha sido reenvidado');
    }
}

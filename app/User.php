<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use App\Transformers\UserTransformer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    const usuario_verificado = '1';
    const usuario_no_verificado = '0';

    const usuario_administrador = 'true';
    const usuario_regular = 'false';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $table = 'users';

    protected $dates = ['delete_at'];

    public $transformer = UserTransformer::class;

    protected $fillable = [
        'name',
        'email',
        'password',
        'verified',
        'verification_token',
        'admin',
    ];

    // mutador para cambiar el atributo name a minuscula
    public function setNameAttribute($valor){
        $this->attributes['name'] = strtolower($valor);
    }

    // accesorio para poner la primera letra a mayusucla
    public function getNameAttribute($valor){
        return ucwords($valor); // => para que  cada palabra tenga una letra en mayuscula
        // return ucfirst($valor); // => para que la primera palabra tenga una mayuscula
    }

    // mutador para cambiar el email a minusculas
    public function setEmailAttribute($valor){
        $this->attributes['email'] = strtolower($valor);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 
        'remember_token',
        'verification_token',
    ];

    public function esVerificado(){
        return $this->verified = User::usuario_verificado;
    }

    public function esAdministrador(){
        return $this->admin = User::usuario_administrador;
    }


    public static function generarVerificacionToken(){
        return str_random(40);
    }

}

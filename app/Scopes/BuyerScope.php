<?php

/* 
	los scopes se hacen para implementar una funcion, esto se usa cuando se trabaja con la inyeccion al modelo, un ejemplo seria este
	public function show(User $user){
		return $user
	}
*/

namespace App\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class BuyerScope implements Scope
{
	public function apply(Builder $builder, Model $model){
		$builder->has('transactions');
	}
}

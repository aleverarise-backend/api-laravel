<?php

namespace App;

use App\Transaction;
use App\Scopes\BuyerScope;
use App\Transformers\BuyerTransformer;

class Buyer extends User
{
    //


    public $transformer = BuyerTransformer::class;

	/*
		el metodo boot es una funcion de los modelos la cual es la que inicializa el modelo
	*/
    protected static function boot(){
    	parent::boot();
    	static::addGlobalScope(new BuyerScope);
    }

    public function transactions(){
    	return $this->hasMany(Transaction::class);
    }
}

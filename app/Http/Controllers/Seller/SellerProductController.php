<?php

namespace App\Http\Controllers\Seller;

use App\User;
use App\Seller;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Transformers\ProductTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{


    public function __construct(){
        parent::__construct();

        $this->middleware('scope:manage-products')->except('index');

        $this->middleware('transform.input:' . ProductTransformer::class)->only(['store', 'update']);

        $this->middleware('can:view,seller')->only('index');
        $this->middleware('can:sale,seller')->only('store');
        $this->middleware('can:edit-product,seller')->only('update');
        $this->middleware('can:delete-product,seller')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        if (request()->user()->tokenCan('read-general') || request()->user()->tokenCan('manage-products')) {
            $products = $seller->products;
            return $this->showAll($products);
        }
        throw new AuthenticationException;
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)
    {
        $rules = [
            'name'        => 'required',
            'description' => 'required',
            'quantity'    => 'required|integer|min:1',
            'image'       => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['status']    = Product::producto_no_disponible;
        // store('carpeta o ruta', 'sistema de archivos a utilizar')
        $data['image']     = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status'   => 'in: ' . Product::producto_disponible . ',' . Product::producto_no_disponible,
            'image'    => 'image',
        ];

        $this->validate($request, $rules);

        // if ($seller->id != $product->seller_id) {
        //     return $this->errorResponse('El vendedor especificado no es el vendedor real del producto', 422);
        // }

        $this->verificarVendedor($seller, $product);

        $product->fill($request->only([
            'name',
            'description',
            'quantity',
        ]));

        if ($request->has('status')) {
            $product->status = $request->status;
            if($product->estaDisponible() && $product->categories()->count() == 0){
                return $this->errorResponse('un producto activo debe tener al menos un categoria', 409);
            }
        }

        if($request->hasFile('image')){
            Storage::delete($product->image);
            $product->image = $request->image->store('');
        }

        if($product->isclean()){
            return $this->errorResponse('se debe especificar al menos un valor diferente para actualizar', 422);
        }

        $product->save();

        return $this->showOne($product);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->verificarVendedor($seller, $product);
        Storage::delete($product->image);
        $product->delete();
        return $this->showOne($product);
    }

    protected function verificarVendedor(Seller $seller, Product $product){
        if ($seller->id != $product->seller_id) {
            throw new HttpException(422, 'especificado no es el vendedor real del producto');
        }
    }
}

<?php

use App\Category;
use App\Product;
use App\Transaction;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        User::truncate();
        Category::truncate();
        Product::truncate();
        Transaction::truncate();
        DB::table('category_product')->truncate();

        User::flushEventListeners();
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        $usuarios      = 1000;
        $categorias    = 30;
        $productos     = 1000;
        $transacciones = 1000;

        factory(User::class, $usuarios)->create();
        factory(Category::class, $categorias)->create();
        factory(Product::class, $categorias)->create()->each(function($producto){
        	$categorias = Category::all()->random(mt_rand(1, 5))->pluck('id');

        	$producto->categories()->attach($categorias);
        });
        factory(Transaction::class, $transacciones)->create();

    }
}

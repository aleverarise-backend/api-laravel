<?php

use App\User;
use App\Seller;
use App\Product;
use App\Category;
use App\Transaction;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
 */

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'name'               => $faker->name,
        'email'              => $faker->unique()->safeEmail,
        'password'           => bcrypt('123456'), // secret
        'remember_token'     => str_random(10),
        'verified'           => $verificado = $faker->randomElement([User::usuario_verificado, User::usuario_no_verificado]),
        'verification_token' => $verificado == User::usuario_verificado ? null : User::generarVerificacionToken(),
        'admin'              => $faker->randomElement([User::usuario_administrador, User::usuario_regular]),
    ];
});

$factory->define(App\Category::class, function (Faker $faker) {
    return [
        'name'  => $faker->word,
        'description' => $faker->paragraph(1),
    ];
});


$factory->define(App\Product::class, function (Faker $faker) {
    return [
        'name'  => $faker->word,
        'description' => $faker->paragraph(1),
        'quantity'  => $faker->numberBetween(1, 10),
        'status' => $faker->randomElement([Product::producto_disponible, Product::producto_no_disponible]),
        'image' => $faker->randomElement(['1.jpg', '2.jpg', '3.jpg']),
        // 'seller_id' => User::inRandomOrder()->first()->id,
        'seller_id' => User::all()->random()->id,
    ];
});


$factory->define(App\Transaction::class, function (Faker $faker) {
    
    $vendedor = Seller::has('products')->get()->random();
    $comprador = User::all()->except($vendedor->id)->random();

    return [
        'quantity'  => $faker->numberBetween(1, 3),
        'buyer_id' => $comprador->id,
        'product_id' => $vendedor->products->random()->id,
    ];
});

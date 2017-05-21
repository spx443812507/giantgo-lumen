<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->email
    ];
});

$factory->define(App\Models\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->name,
        'display_name' => $faker->name,
        'description' => $faker->title
    ];
});

$factory->define(App\Models\Permission::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->unique()->name,
        'display_name' => $faker->name,
        'description' => $faker->title
    ];
});

$factory->define(App\Models\Product::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->unique()->name,
        'description' => $faker->name,
        'price' => $faker->title
    ];
});
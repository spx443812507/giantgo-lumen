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

$factory->define(App\Models\EAV\Factories\EntityFactory::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->unique(),
        'entity_type_name' => $faker->name,
        'entity_type_code' => $faker->name,
        'entity_model' => $faker->name,
        'entity_table' => $faker->name,
        'description' => $faker->title
    ];
});


$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->unique()->email,
        'mobile' => $faker->unique()->phoneNumber
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

$factory->define(App\Models\Application::class, function (Faker\Generator $faker) {
    return [
        'client_id' => $faker->unique()->name,
        'client_secret' => $faker->name,
        'redirect' => $faker->title,
        'provider' => $faker->title
    ];
});


$factory->define(App\Models\EAV\Attribute::class, function (Faker\Generator $faker) {
    return [
        'entity_type_id' => $faker->unique(),
        'attribute_code' => $faker->unique()->name,
        'backend_model' => $faker->name,
        'backend_type' => $faker->title,
        'backend_table' => $faker->title,
        'frontend_model' => $faker->title,
        'frontend_input' => $faker->title,
        'frontend_label' => $faker->title,
        'frontend_class' => $faker->title,
        'is_required' => $faker->boolean,
        'is_user_defined' => $faker->boolean,
        'is_unique' => $faker->boolean,
        'default_value' => $faker->title,
        'description' => $faker->title
    ];
});
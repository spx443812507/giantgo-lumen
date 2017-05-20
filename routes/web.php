<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$prefix = env('API_PREFIX');

$app->get($prefix . '/', function () use ($app) {
    return phpinfo();
});

$app->group(['prefix' => $prefix], function () use ($app) {
    $app->group(['prefix' => 'passports'], function () use ($app) {
        $app->patch('/', 'PassportController@signIn');
        $app->post('/', 'PassportController@signUp');
    });

    $app->group(['middleware' => 'auth'], function () use ($app) {
        $app->get('users/me', 'UserController@me');

        $app->group(['middleware' => 'role:admin'], function () use ($app) {
            $app->get('users', 'UserController@getList');

            $app->group(['prefix' => 'roles'], function () use ($app) {
                $app->post('/', 'RoleController@create');
            });
        });

        $app->group(['middleware' => 'role:owner'], function () use ($app) {

        });

        $app->group(['middleware' => 'role:customer'], function () use ($app) {

        });
    });
});

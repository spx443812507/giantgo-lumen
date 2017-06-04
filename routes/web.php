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

$app->group(['prefix' => $prefix, 'middleware' => 'cors'], function () use ($app) {
    $app->group(['prefix' => 'passports'], function () use ($app) {
        $app->patch('/', 'PassportController@signIn');
        $app->post('/', 'PassportController@signUp');
    });

    $app->group(['middleware' => 'auth'], function () use ($app) {
        $app->get('/users/me', 'UserController@me');
        $app->get('/users', ['as' => 'users.get', 'uses' => 'UserController@getList', 'middleware' => 'role:admin']);

        $app->post('/roles', ['as' => 'roles.create', 'uses' => 'RoleController@create', 'middleware' => 'permission:role-create']);

        $app->post('/products', ['as' => 'products.create', 'uses' => 'ProductController@create', 'middleware' => 'permission:product-create']);
    });

    $app->get('/products/export', ['as' => 'products.export', 'uses' => 'ProductController@export']);

    $app->group(['prefix' => 'oauth'], function () use ($app) {
        $app->get('/applications/{appId}/wechat/login', 'OAuthController@weChatLogin');
        $app->get('/applications/{appId}/wechat/callback', 'OAuthController@weChatCallback');
    });

    $app->get('/socials/me', 'SocialAccountController@me');
});

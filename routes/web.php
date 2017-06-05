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

    $app->get('/socials/me', 'SocialAccountController@me');
});

$app->group(['prefix' => 'oauth', 'namespace' => 'OAuth'], function () use ($app) {
    $app->get('/wechat/login', 'WeChatController@weChatLogin');
    $app->get('/wechat/callback', 'WeChatController@weChatCallback');
    $app->get('/qq/login', 'QQController@qqLogin');
    $app->get('/qq/callback', 'QQController@qqCallback');
});
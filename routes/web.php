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
        $app->patch('/users/me', 'UserController@me');
        $app->get('/users/{userId}', ['as' => 'users.get', 'uses' => 'UserController@get', 'middleware' => 'role:admin']);
        $app->get('/users', ['as' => 'users.getList', 'uses' => 'UserController@getList', 'middleware' => 'role:admin']);
        $app->patch('/users', ['as' => 'user.update', 'uses' => 'UserController@updateUser']);

        $app->post('/roles', ['as' => 'roles.create', 'uses' => 'RoleController@create', 'middleware' => 'permission:role-create']);

        $app->post('/products', ['as' => 'products.create', 'uses' => 'ProductController@create', 'middleware' => 'permission:product-create']);

        $app->post('/entities', ['as' => 'entities.create', 'uses' => 'EntityController@createEntity', 'middleware' => 'role:admin']);

        $app->post('/attributes/batch', ['as' => 'attributes.batchCreate', 'uses' => 'AttributeController@createAttributes']);
        $app->post('/attributes', ['as' => 'attributes.create', 'uses' => 'AttributeController@createAttribute']);
        $app->put('/attributes', ['as' => 'attributes.update', 'uses' => 'AttributeController@updateAttribute']);
        $app->get('/attributes', ['as' => 'attributes.get', 'uses' => 'AttributeController@getAttributes']);
    });

    $app->get('/products/export', ['as' => 'products.export', 'uses' => 'ProductController@export']);

    $app->get('/socials/me', 'SocialAccountController@me');
});

$app->get('/oauth/{provider}/login', 'OAuthController@login');
$app->get('/oauth/{provider}/callback', 'OAuthController@callback');

$app->get('/oauth/wechat/open/login', 'PlatformController@login');
$app->get('/oauth/wechat/open/callback', 'PlatformController@callback');
$app->get('/oauth/wechat/open/auth', 'PlatformController@auth');
$app->get('/oauth/wechat/open/response', 'PlatformController@response');


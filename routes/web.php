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

    $app->group(['prefix' => 'contacts'], function () use ($app) {
        $app->patch('/', 'ContactController@signIn');
        $app->post('/', 'ContactController@signUp');
    });

    $app->group(['middleware' => 'auth:web'], function () use ($app) {
        $app->get('/users/me', ['as' => 'users.me', 'uses' => 'UserController@me']);
        $app->get('/users/{userId}', ['as' => 'users.get', 'middleware' => ['role:admin'], 'uses' => 'UserController@get']);
        $app->get('/users', ['as' => 'users.getList', 'middleware' => ['role:admin'], 'uses' => 'UserController@getList']);
        $app->patch('/users/{user_id}', ['as' => 'user.update', 'uses' => 'UserController@updateUser']);

        $app->post('/roles', ['as' => 'roles.create', 'uses' => 'RoleController@createRole', 'middleware' => ['ability:admin,role-create']]);
        $app->get('/roles/{role_id}', ['as' => 'roles.get', 'uses' => 'RoleController@getRole', 'middleware' => ['ability:admin,role-get']]);

        $app->post('/entities', ['as' => 'entities.create', 'uses' => 'EntityController@createEntity', 'middleware' => ['ability:admin,entity-create']]);
        $app->get('/entities/{entity_type_code}', ['as' => 'entities.list', 'uses' => 'EntityController@getEntityList', 'middleware' => ['ability:admin,entity-list']]);

        $app->post('/attributes/batch', ['as' => 'attributes.batchCreate', 'uses' => 'AttributeController@createAttributes']);
        $app->post('/attributes', ['as' => 'attributes.create', 'uses' => 'AttributeController@createAttribute']);
        $app->put('/attributes', ['as' => 'attributes.update', 'uses' => 'AttributeController@updateAttribute']);
        $app->get('/attributes', ['as' => 'attributes.get', 'uses' => 'AttributeController@getAttributes']);

        $app->get('/seminars', ['as' => 'seminar.list', 'uses' => 'SeminarController@getSeminarList']);
        $app->get('/seminars/{seminar_id}', ['as' => 'seminar.get', 'uses' => 'SeminarController@getSeminar']);
        $app->post('/seminars', ['as' => 'seminar.create', 'uses' => 'SeminarController@createSeminar']);
        $app->patch('/seminars/{seminar_id}', ['as' => 'seminar.update', 'uses' => 'SeminarController@updateSeminar']);

    });

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->get('/contacts/me', ['as' => 'contacts.me', 'uses' => 'ContactController@me']);

    });

    $app->get('/socials/me', 'SocialAccountController@me');
});

$app->get('/oauth/{provider}/login', 'OAuthController@login');
$app->get('/oauth/{provider}/callback', 'OAuthController@callback');

$app->get('/oauth/wechat/open/login', 'PlatformController@login');
$app->get('/oauth/wechat/open/callback', 'PlatformController@callback');
$app->get('/oauth/wechat/open/auth', 'PlatformController@auth');
$app->get('/oauth/wechat/open/response', 'PlatformController@response');

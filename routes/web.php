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

$app->get('/', function () use ($app) {
    return phpinfo();
});

$app->get('users/{id}', 'UserController@get');
$app->get('users/me', 'UserController@me');
$app->get('users', 'UserController@getList');

$app->patch('passports', 'PassportController@signIn');
$app->post('passports', 'PassportController@signUp');
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
    $app->post('/user', ['as' => 'user.create', 'uses' => 'UserController@signUp']);
    $app->patch('/user', ['as' => 'user.update', 'uses' => 'UserController@signIn']);

    $app->post('/contact', ['as' => 'user.create', 'uses' => 'ContactController@signUp']);
    $app->patch('/contact', ['as' => 'user.update', 'uses' => 'ContactController@signIn']);

    $app->get('/seminars', ['as' => 'seminars.list', 'uses' => 'SeminarController@getSeminarList']);
    $app->get('/seminars/{seminar_id}', ['as' => 'seminars.get', 'uses' => 'SeminarController@getSeminar']);

    $app->get('/seminars/{seminar_id}/agendas', ['as' => 'agendas.list', 'uses' => 'AgendaController@getAgendaList']);
    $app->get('/seminars/{seminar_id}/agendas/{agenda_id}', ['as' => 'agendas.get', 'uses' => 'AgendaController@getAgenda']);

    $app->get('/seminars/{seminar_id}/speakers', ['as' => 'speakers.list', 'uses' => 'SpeakerController@getSpeakerList']);
    $app->get('/seminars/{seminar_id}/speakers/{speaker_id}', ['as' => 'speakers.get', 'uses' => 'SpeakerController@getSpeaker']);

    $app->get('/search/speakers', ['as' => 'search.speakers', 'uses' => 'SpeakerController@searchSpeakerList']);

    $app->post('/files/avatar', ['as' => 'avatar.create', 'uses' => 'FileController@uploadAvatar']);

    $app->group(['middleware' => 'auth:web'], function () use ($app) {
        $app->get('/users/{user_id}', ['as' => 'users.get', 'middleware' => ['role:admin'], 'uses' => 'UserController@get']);
        $app->get('/users', ['as' => 'users.getList', 'middleware' => ['role:admin'], 'uses' => 'UserController@getList']);
        $app->put('/users/{user_id}', ['as' => 'user.update', 'uses' => 'UserController@updateUser']);
        $app->get('/user', ['as' => 'user.get', 'uses' => 'UserController@me']);

        $app->post('/roles', ['as' => 'roles.create', 'uses' => 'RoleController@createRole', 'middleware' => ['ability:admin,role-create']]);
        $app->get('/roles/{role_id}', ['as' => 'roles.get', 'uses' => 'RoleController@getRole', 'middleware' => ['ability:admin,role-get']]);

        $app->post('/entities', ['as' => 'entities.create', 'uses' => 'EntityController@createEntity', 'middleware' => ['ability:admin,entity-create']]);
        $app->get('/entities/{entity_type_code}', ['as' => 'entities.list', 'uses' => 'EntityController@getEntityList', 'middleware' => ['ability:admin,entity-list']]);

        $app->post('/entities/{entity_type_id}/attributes/batch', ['as' => 'attributes.batchCreate', 'uses' => 'AttributeController@batchCreateAttribute']);
        $app->post('/entities/{entity_type_id}/attributes', ['as' => 'attributes.create', 'uses' => 'AttributeController@createAttribute']);
        $app->put('/entities/{entity_type_id}/attributes/{attribute_id}', ['as' => 'attributes.update', 'uses' => 'AttributeController@updateAttribute']);
        $app->get('/entities/{entity_type_id}/attributes', ['as' => 'attributes.list', 'uses' => 'AttributeController@getAttributeList']);
        $app->delete('/entities/{entity_type_id}/attributes/{attribute_id}', ['as' => 'attributes.delete', 'uses' => 'AttributeController@deleteAttribute']);

        $app->post('/seminars', ['as' => 'seminars.create', 'uses' => 'SeminarController@createSeminar', 'middleware' => ['ability:admin,seminar-create']]);
        $app->put('/seminars/{seminar_id}', ['as' => 'seminars.update', 'uses' => 'SeminarController@updateSeminar', 'middleware' => ['ability:admin,seminar-edit']]);
        $app->delete('/seminars/{seminar_id}', ['as' => 'seminars.delete', 'uses' => 'SeminarController@deleteSeminar', 'middleware' => ['ability:admin,seminar-delete']]);

        $app->post('/seminars/{seminar_id}/agendas', ['as' => 'agendas.create', 'uses' => 'AgendaController@createAgenda', 'middleware' => ['ability:admin,agenda-create']]);
        $app->put('/seminars/{seminar_id}/agendas/{agenda_id}', ['as' => 'agendas.update', 'uses' => 'AgendaController@updateAgenda', 'middleware' => ['ability:admin,agenda-edit']]);
        $app->delete('/seminars/{seminar_id}/agendas/{agenda_id}', ['as' => 'agendas.delete', 'uses' => 'AgendaController@deleteAgenda', 'middleware' => ['ability:admin,agenda-delete']]);

        $app->post('/seminars/{seminar_id}/speakers', ['as' => 'speakers.create', 'uses' => 'SpeakerController@createSpeaker', 'middleware' => ['ability:admin,speaker-create']]);
        $app->put('/seminars/{seminar_id}/speakers/{speaker_id}', ['as' => 'speakers.update', 'uses' => 'SpeakerController@updateSpeaker', 'middleware' => ['ability:admin,speaker-update']]);
        $app->delete('/seminars/{seminar_id}/speakers/{speaker_id}', ['as' => 'speakers.delete', 'uses' => 'SpeakerController@deleteSpeaker', 'middleware' => ['ability:admin,speaker-delete']]);

        $app->get('/seminars/{seminar_id}/checkins/{checkin_id}', ['as' => 'checkins.get', 'uses' => 'CheckinController@getCheckin', 'middleware' => ['ability:admin,checkin-get']]);
        $app->get('/seminars/{seminar_id}/checkins', ['as' => 'checkins.list', 'uses' => 'CheckinController@getCheckinList', 'middleware' => ['ability:admin,checkin-list']]);
        $app->post('/seminars/{seminar_id}/checkins', ['as' => 'checkins.create', 'uses' => 'CheckinController@createCheckin', 'middleware' => ['ability:admin,checkin-create']]);
        $app->put('/seminars/{seminar_id}/checkins/{checkin_id}', ['as' => 'checkins.update', 'uses' => 'CheckinController@updateCheckin', 'middleware' => ['ability:admin,checkin-update']]);
        $app->delete('/seminars/{seminar_id}/checkins/{checkin_id}', ['as' => 'checkins.delete', 'uses' => 'CheckinController@deleteCheckin', 'middleware' => ['ability:admin,checkin-delete']]);

        $app->get('/contacts', ['as' => 'contacts.list', 'uses' => 'ContactController@getList', 'middleware' => ['ability:admin,contact-list']]);
        $app->get('/contacts/{contact_id}', ['as' => 'contacts.get', 'uses' => 'ContactController@get', 'middleware' => ['ability:admin,contact-get']]);
    });

    $app->group(['middleware' => 'auth:api'], function () use ($app) {
        $app->get('/contact', ['as' => 'contacts.get', 'uses' => 'ContactController@me']);
        $app->put('/contact', ['as' => 'contacts.update', 'uses' => 'ContactController@updateMyInfo']);
        $app->get('/contact/social_account', ['as' => 'social_account.get', 'uses' => 'SocialAccountController@get']);
    });
});

$app->get('/oauth/{provider}/login', 'OAuthController@login');
$app->get('/oauth/{provider}/callback', 'OAuthController@callback');

$app->get('/oauth/wechat/open/login', 'PlatformController@login');
$app->get('/oauth/wechat/open/callback', 'PlatformController@callback');
$app->get('/oauth/wechat/open/auth', 'PlatformController@auth');
$app->get('/oauth/wechat/open/response', 'PlatformController@response');

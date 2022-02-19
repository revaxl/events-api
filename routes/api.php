<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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
// redirect from / to /api/events
$router->get('/', function () {
    return redirect('api/events');
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'events'], function () use ($router) {
        $router->get('/', 'EventController@index');
        $router->post('{eventId}/reservation', 'EventController@reserve');
        $router->put('{eventId}/reservation', 'EventController@update');
        $router->delete('{eventId}/cancel', 'EventController@cancel');
    });
});

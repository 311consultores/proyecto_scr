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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    //Rutas Admin Panel
    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->post('login', 'AdminController@login');
        $router->get('getReports', 'AdminController@getReports');
    });
    // Rutas de Bitacora
    $router->group(['prefix' => 'bitacora'], function () use ($router) {
        $router->get('index','BitacoraController@index');
        $router->post('recuperarFolio','BitacoraController@recuperarFolio');
        $router->post('guardarBitacora','BitacoraController@guardarBitacora');
        $router->post('guardarDetBitacora', 'BitacoraController@guardarDetBitacora');
        $router->get('exportarPDF/{id}', 'BitacoraController@exportPDF');

        $router->group(['prefix' => 'admin'], function () use ($router) {
            $router->get('index', 'BitacoraController@adminIndex');
        });
    });
    //Reportes
    $router->group(['prefix' => 'reportes'], function () use ($router) {
        $router->get("getReportesEvidencia", 'ReporteController@getReportesEvidencia');
        $router->get("getPDFReportEvidencia/{id_reporte}", 'ReporteController@getPDFReportEvidencia');
        $router->post('guardarReporteEvidencia', 'ReporteController@guardarReporteEvidencia');
    });
});
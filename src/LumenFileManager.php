<?php

namespace Betalectic\FileManager;

use Laravel\Lumen\Application;
use Laravel\Lumen\Routing\Router;

class LumenFileManager {

    public static function routes($app)
    {
	    $app->router->get('file-manager/tags', 'Betalectic\FileManager\Http\Controllers\FileController@tags');
	    $app->router->get('file-manager', 'Betalectic\FileManager\Http\Controllers\FileController@index');
	    $app->router->delete('file-manager/{id}', 'Betalectic\FileManager\Http\Controllers\FileController@destroy');
	    $app->router->put('file-manager/{id}', 'Betalectic\FileManager\Http\Controllers\FileController@update');
	    $app->router->post('file-manager/upload-save', 'Betalectic\FileManager\Http\Controllers\UploadSaveController@store');

	    return $app;
    }
}
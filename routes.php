<?php


    Route::get('file-manager/tags', 'Betalectic\FileManager\Http\Controllers\FileController@tags');
    Route::get('file-manager', 'Betalectic\FileManager\Http\Controllers\FileController@index');
    Route::delete('file-manager/{id}', 'Betalectic\FileManager\Http\Controllers\FileController@destroy');
    Route::put('file-manager/{id}', 'Betalectic\FileManager\Http\Controllers\FileController@update');
    Route::post('file-manager/upload-save', 'Betalectic\FileManager\Http\Controllers\UploadSaveController@store');
	



<?php

use Illuminate\Support\Facades\Route;

Route::group([

	'prefix' => 'file-manager',
	'namespace' => 'OrckidLab\FileManager\Core\Controllers'

], function () {
	Route::match(['get', 'post'], '/', 'FileManagerController@index');

	/*Route::post('/', 'FileManagerController@index');*/
	
	Route::post('upload', 'FileController@store');

	Route::post('create', 'DirectoryController@store');

	Route::post('delete', 'FileManagerController@destroy');

	Route::post('search', 'FileManagerFilterController@index');

	Route::post('update', 'FileManagerController@update');

	Route::post('move', 'FileManagerController@update');

	Route::post('crop', 'FileManagerController@update');

	Route::post('optimise', 'FileManagerController@update');

	Route::post('download', 'DownloadController@index');
});
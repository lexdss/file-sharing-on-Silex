<?php

//Index controller routing
$index_controller = $app['controllers_factory'];
$index_controller->get('/', function() use ($app){
	$controller = new App\Controller\IndexController($app); 
	return $controller->index();
})
->bind('main');

$index_controller->post('/', function(Request $request) use ($app){
	$controller = new App\Controller\IndexController($app);
	return $controller->upload($request);
});

$index_controller->get('/download/{file_id}', function($file_id) use ($app){
	$controller = new App\Controller\IndexController($app); 
	return $controller->download($file_id);
});

$index_controller->post('/save/{file_id}', function($file_id, Request $request) use ($app){
	$controller = new App\Controller\IndexController($app); 
	return $controller->save($file_id, $request);
});

$index_controller->get('/delete/{file_id}', function($file_id) use ($app){
	$controller = new App\Controller\IndexController($app); 
	return $controller->delete($file_id);
});

$index_controller->get('/lastfiles', function() use ($app){
	$controller = new App\Controller\IndexController($app); 
	return $controller->lastFiles();
})
->bind('lastfiles');

$index_controller->get('/{file_id}', function($file_id) use ($app){
	$controller = new App\Controller\IndexController($app); 
	return $controller->fileDetail($file_id);
});

$app->mount('/', $index_controller);
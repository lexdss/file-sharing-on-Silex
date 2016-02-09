<?php
use Symfony\Component\HttpFoundation\Request;

define('ROOT_DIR', $_SERVER['DOCUMENT_ROOT']);
define('UPLOAD_DIR', ROOT_DIR . '/upload/'); //Директория для файлов загрузок
define('UPLOAD_PATH', '/upload/'); //Путь к папке загрузок для урлов

$loader = require_once ROOT_DIR . '/vendor/autoload.php';
$db_config = parse_ini_file(ROOT_DIR . '/App/db_config.ini');

$app = new Silex\Application();
$app['debug'] = true;

$app['autoloader'] =  $app->share(function() use($loader){return $loader;});
$app['autoloader']->add("App",ROOT_DIR);


$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => ROOT_DIR . '/view'));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array('db.options' => array(
		'host' => $db_config['DB_HOST'],
		'dbname' => $db_config['DB_NAME'],
		'password' => $db_config['DB_PASSWORD'],
		'user' => $db_config['DB_USER'],
		'charset' => $db_config['DB_CHARSET']
	)));

$app['file_service'] = function(){return new App\Service\FileService();};
$app['getid3'] = function(){return new getID3();};
$app['file_mapper'] = function() use($app){
	$qb = $app['db']->createQueryBuilder();
	return new App\Mapper\FileMapper($qb);
};

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

$app->run();
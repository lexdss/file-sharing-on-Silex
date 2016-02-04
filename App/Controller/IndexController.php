<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use App\Entity\File;

class IndexController{

    public $app;

    public function __construct($app){
        $this->app = $app;
    }

    public function index(){
    	return $this->app['twig']->render('main.html');
    }

    //Загрузка на сервер и сохранение в БД
    public function upload($request){
    	$uploaded_file = $request->files->get('upload_file');
        //Информация о загруженном файле
        $file_info = $this->app['getid3']->analyze($uploaded_file);
        //Проверка на ошибки
        if($result = $this->app['file_service']->getError($uploaded_file)){
            return $this->app['twig']->render('main.html', array('message' => $result));
        }
    	$file = new File;
        //Основная информация
        $file->id = uniqid();
        $file->name = $this->app['file_service']->getName($uploaded_file->getClientOriginalName());
        $file->type = $this->app['file_service']->getType($file_info);
        $file->extension = $uploaded_file->getClientOriginalExtension();
        $file->size = $file_info['filesize'];
        $file->mime_type = $file_info['mime_type'];
        $file->upload_date = date('Y-m-d', time());
        //Информация в зависимости от типа файла
        if($file->type == 'image' || $file->type == 'video'){
            $file->width = $this->app['file_service']->getWidth($file_info);
            $file->height = $this->app['file_service']->getHeight($file_info);
        }
        if($file->type == 'video' || $file->type == 'audio'){
            $file->playtime = $this->app['file_service']->getPlaytime($file_info);
            $file->bitrate = $this->app['file_service']->getBitrate($file_info);
        }
        //Перемещени файла и изменение расширения на txt для безопасности
        $uploaded_file->move(UPLOAD_DIR, $file->id . '.txt');
        //Сохранение в БД
        $this->app['file_mapper']->save($file);
        //Сохраняются куки
        $response = new Response;
        $response->headers->setCookie(new Cookie($file->id, true, time() + 3600 * 24));
        $response->send();
        return $this->app->redirect('/' . $file->id);
    }

    //Детальная страница файла
    public function fileDetail($file_id){
        if($file = $this->app['file_mapper']->getFile($file_id)){
            $title = 'Скачать - ' . $file->name;
            //Переводим размер с байтов
            $file->size = $this->app['file_service']->getSize($file->size);
            return $this->app['twig']->render('file_detail.html', array('title' => $title, 'file' => $file));
        }else{
            $this->app->abort(404, 'File not found');
        }
    }

    //Отдает файл
    public function download($file_id){
        if(!$file = $this->app['file_mapper']->getFile($file_id)){
            $this->app->abort(404, 'File not found');
        }
        $file_path = UPLOAD_DIR . $file->id . '.txt';
        if(!file_exists($file_path)){
            $this->app->abort(404, 'File not found');
        }
        $fr = fopen($file_path, 'r');
        $response = new Response();
        $response->setContent(fread($fr, filesize($file_path)));
        $response->headers->set('Content-Type', $file->mime_type);
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $file->name . '.' .$file->extension);
        $response->send();
    }

    //Сохранение описания файла
    public function save($file_id, $request){
        if(!$file = $this->app['file_mapper']->getFile($file_id)){
            $this->app->abort(404, 'File not found');
        }
        $file->description = $request->get('description');
        $this->app['file_mapper']->save($file);
        return $this->app->redirect('/' . $file->id);
    }

    //Удаление файла
    public function delete($file_id){
        $this->app['file_mapper']->delete($file_id);
        unlink(UPLOAD_DIR . $file_id . '.txt');
        $response = new Response;
        $response->headers->setCookie(new Cookie($file_id, null, time() - 1));
        return $this->app->redirect('/');
    }

    //Послеедние загрузки
    public function lastFiles(){
        $files = $this->app['file_mapper']->getLastFiles();
        //Перевод размеров файлов с байтов
        foreach($files as $key => $file){
            $files[$key]['size'] = $this->app['file_service']->getSize($file['size']);
        }
        return $this->app['twig']->render('last_files.html', array('files' => $files));
    }
}
<?php
namespace App\Service;

use Silex\Application;
use App\Entity\File;

class FileService{

	//Возвращает имя файла без расширения
	public function getName($name){
		$file_arr = explode('.', $name);
		unset($file_arr[count($file_arr) - 1]);
		$file_name = str_replace(' ', '_', implode($file_arr, '.')); //Меняем пробелы на подчеркивания
		return $file_name;
	}

	public function getType($file_info){
		if(strstr($file_info['mime_type'], 'image')){
			$type = 'image';
		}elseif(strstr($file_info['mime_type'], 'video')){
			$type = 'video';
		}elseif(strstr($file_info['mime_type'], 'audio')){
			$type = 'audio';
		}else{
			$type = 'file';
		}
		return $type;
	}

	//Переводит битрейт к kbps
	public function changeBitrate($bitrate){
		$bitrate /= 1000;
		return (int)$bitrate;
	}

	//Переводит размер с байтов на килобайты или мегабайты
	public function getSize($size){
		if($size >= 1000000){
			$size /= 1000000;
			$size = sprintf('%0.1f mb', $size);
		}elseif($size >= 1000){
			$size /= 1000;
			$size = sprintf('%d kb', $size);
		}else{
			$size .= ' b';
		}
		return $size;
	}
	public function getWidth($file_info){
		return (isset($file_info['video']['resolution_x'])) ? $file_info['video']['resolution_x'] : null;
	}

	public function getHeight($file_info){
		return (isset($file_info['video']['resolution_y'])) ? $file_info['video']['resolution_y'] : null;
	}

	public function getPlaytime($file_info){
		return (isset($file_info['playtime_string'])) ? $file_info['playtime_string'] : null;
	}

	public function getBitrate($file_info){
		return (isset($file_info['bitrate'])) ? $this->changeBitrate($file_info['bitrate']) : null;
	}

	//Проверка на ошибки при загрузке файла
	public function getError($uploaded_file){
		if(!isset($uploaded_file)){
	         $message = 'Please choose file!';
	     }elseif($uploaded_file->getError() == 1 || $uploaded_file->getError() == 2){
	         $message = 'File size too large. Max size - 100 Mb';
	     }elseif(!$uploaded_file->isValid() || $uploaded_file->getError() != 0){
	         $message = 'Upload error';
	     }
	   return (!empty($message)) ? $message : false;
	}
}
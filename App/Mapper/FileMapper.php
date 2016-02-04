<?php
namespace App\Mapper;

use App\Entity\File;

class FileMapper{

	private $qb;
	private $file;

	public function __construct($qb){
		$this->qb = $qb;
		$this->file = new File;
	}

	//Добавление или обновление записи о файле
	public function save($file){
		if(!empty($file->description)){
			$result = $this->qb->update('files')
			->set('description', ':description')
			->where('id = :id')
			->setParameter(':description', $file->description)
			->setParameter(':id', $file->id)
			->execute();
		}else{
			//Массив с плейсхолдерами
			foreach($file as $param => $value){
				$prepare[$param] = ':' . $param;
			}
			$result = $this->qb->insert('files')
			->values($prepare)
			->setParameters((array)$file)
			->execute();
		}
		return $result;
	}

	public function getFile($file_id){
		$sth = $this->qb->select('*')
		->from('files')
		->where('id = :id')
		->setParameter(':id', $file_id)
		->execute();
		if(!$result = $sth->fetch()){
			return false;
		}else{
			foreach($result as $param => $value){
				$this->file->$param = $value;
			}
			return $this->file;
		}
	}

	public function delete($file_id){
		$result = $this->qb->delete('files')
		->where('id = :id')
		->setParameter(':id', $file_id)
		->execute();
		return $result;
	}

	public function getLastFiles(){
		$sth = $this->qb->select('id', 'name', 'size', 'extension', 'upload_date')
		->from('files')
		->orderBy('id', 'DESC')
		->execute();
		return $sth->fetchAll();
	}
}
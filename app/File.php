<?php
namespace App;
use App\QueryBuilder;
    class File{
        private $queryBuilder;

        public function __construct(QueryBuilder $queryBuilder){
            $this->queryBuilder = $queryBuilder;
        }
        //upload($id, $_FILES['image'], $table, 'img/demo/avatars/');

        public function upload($id, $file, $prev_img, $table, $path){
            $from = $file['tmp_name'];
            $name = $file['name'];
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $file_name = uniqid().'.'.$ext; 
            $to = $path.$file_name;
            move_uploaded_file($from, $to);
            if($prev_img != 'avatar-m.png'){
                unlink($path.$prev_img);
            }
            $this->queryBuilder->update($table, ['img' => $file_name], [['user_id = :user_id'], ['user_id' => $id]]);
        }
    }
?>
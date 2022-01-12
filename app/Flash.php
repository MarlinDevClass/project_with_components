<?php
namespace App;
    class Flash{
        public static function get($key){//получение флэш сообщения
            $el = $_SESSION[$key];//запоминаем данные из сессии
            unset($_SESSION[$key]);//удаляем сессию
            return $el;//запоминаем сохранённое значение
        }

        public static function set_few(array $data){//установка нескольких флэш-сообщений
            //параметром передаём массив с флэш-сообщениями
            foreach($data as $key => $el){
                //проходим по массиву, установив каждое сообщение в сессию с соответственным ключом
                $_SESSION[$key] = $el;
            }
        }

        public static function set_one($key, $value){//установка одного флэш сообщения
            //устанавливаем в сессию с соответственным ключом соответственное значение
            $_SESSION[$key] = $value;
        }
    }
?>
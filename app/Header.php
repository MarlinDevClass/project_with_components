<?php
    namespace App;
    class Header{

        public static function redirect($url){
            header("Location: $url");
            die;
        }
    }
?>
<?php
namespace App\Controllers;
use League\Plates\Engine;
use Exception;
use PDO;
use App\QueryBuilder;
use Faker\Factory;
use App\Flash;
use App\Header;
use Delight\Auth\Auth;
use Faker\Provider\en_US\Address;

    class PagesController{
        private $queryBuilder;
        private $templates;
        private $auth;
        private $flash;
        public function __construct(QueryBuilder $queryBuilder, Engine $engine, Auth $auth){
            $this->queryBuilder = $queryBuilder;
            $this->templates = $engine;
            $this->auth = $auth;
        }

        public function create(){
            if (!$this->auth->isLoggedIn()){
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)){
                Header::redirect("/");
            }
            $error = Flash::get('error');
            return $this->templates->render('create', ['error' => $error]);
        }

        public function edit($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $user = $this->queryBuilder->selectDefinite('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            return $this->templates->render('edit', ['user' => $user]);
        }

        public function image($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $user = $this->queryBuilder->selectDefinite('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            return $this->templates->render('image', ['user' => $user]);
        }
        
        public function profile($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            $user1 = $this->queryBuilder->selectDefinite('users', [['id = :id'], ['id' => $id]]);
            $user2 = $this->queryBuilder->selectDefinite('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            return $this->templates->render('profile', ['user1' => $user1, 'user2' => $user2]);
        }

        public function security($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $user = $this->queryBuilder->selectDefinite('users', [['id = :id'], ['id' => $id]]);
            $error = Flash::get('error');
            return $this->templates->render('security', ['user' => $user, 'error' => $error]);
        }

        public function status($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $user = $this->queryBuilder->selectDefinite('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            return $this->templates->render('status', ['user' => $user]);
        }

        public function users(){
            if (!$this->auth->isLoggedIn()){
                Header::redirect("/login");
            }
            echo $this->auth->getUserId().'<br>';
            echo $this->auth->getEmail();
            $success = Flash::get('success');
            $users1 = $this->queryBuilder->selectAll('users');
            $temp = $this->queryBuilder->selectAll('users_information');
            $users2 = [];
            foreach($temp as $el){//заполняем массив таким образом, чтобы ключ его элемента соответствовал id
                $users2[$el['user_id']] = $el;
            }
            
            return $this->templates->render('users', ['users1' => $users1, 'users2' => $users2, 'success' => $success, 'auth' => $this->auth]);
        }

        public function register(){
            $error = Flash::get('error');
            return $this->templates->render('register', ['error' => $error]);
        }

        public function login(){
            $success = Flash::get('success');
            $error = Flash::get('error');
            return $this->templates->render('login', ['success' => $success, 'error' => $error]);
        }
    }
?>
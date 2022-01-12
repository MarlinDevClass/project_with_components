<?php
    namespace App\Controllers;
    use App\Header;
    use Delight\Auth\Auth;
    use Password\Validator;
    use App\QueryBuilder;
    use App\File;
    use App\Flash;

    class AdminController{
        private $auth;
        private $queryBuilder;
        private $file;
        private $validator;

        public function __construct(Auth $auth, QueryBuilder $queryBuilder, File $file, Validator $validator){
            $this->auth = $auth;
            $this->queryBuilder = $queryBuilder;
            $this->file = $file;
            $this->validator = $validator;
        }

        public function create(){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)){
                Header::redirect("/");
            }
            $this->validator->setMinLength(7);
            $this->validator->setMinNumbers(2);
            $this->validator->setMinUpperCaseLetters(2);
            if (!$this->validator->isValid($_POST['password'])) {
                Flash::set_one('error', 'Пароль должен содержать минимум 7 символов, из которых 2 цифры и 2 буквы в верхнем регистре!');
                Header::redirect("/create");
            }
            try {
                $userId = $this->auth->admin()->createUser($_POST['email'], $_POST['password']);
                $this->queryBuilder->insert('users_information', [
                    'user_id' => $userId,
                    'name' => $_POST['name'],
                    'status' => $_POST['status'],
                    'phone' => $_POST['phone'],
                    'position' => $_POST['position'],
                    'address' => $_POST['address'],
                    'vk' => $_POST['vk'],
                    'telegram' => $_POST['telegram'],
                    'instagram' => $_POST['instagram']
                ]);
                if(!empty($_FILES['image'])){
                  $this->file->upload($id, $_FILES['image'], 'avatar-m.png', 'users_information', 'img/demo/avatars/');  
                }

                Flash::set_one('success', 'Пользователь успешно добавлен!');
                Header::redirect("/");
            }
            catch (\Delight\Auth\InvalidEmailException $e) {
                Flash::set_one('error', 'Не действительный email!');
            }
            catch (\Delight\Auth\InvalidPasswordException $e) {
                Flash::set_one('error', 'Не действительный пароль!');
            }
            catch (\Delight\Auth\UserAlreadyExistsException $e) {
                Flash::set_one('error', 'Данный Email уже существует!');
            }
            Header::redirect("/create");
        }
        
        public function setadmin($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)){
                Header::redirect("/");
            }
            try {
                $this->auth->admin()->addRoleForUserById($id, \Delight\Auth\Role::ADMIN);
                Flash::set_one('success', 'Пользователь успешно назначен админом!');
                Header::redirect("/");
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown user ID');
            }
        }

        public function deladmin($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN)){
                Header::redirect("/");
            }
            try {
                $this->auth->admin()->removeRoleForUserById($id, \Delight\Auth\Role::ADMIN);
                Flash::set_one('success', 'Пользователь больше не админ!');
                Header::redirect("/");
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown user ID');
            }
        }
    }
?>
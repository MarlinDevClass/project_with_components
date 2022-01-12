<?php
    namespace App\Controllers;
    use App\Header;
    use Delight\Auth\Auth;
    use PDO;
    use App\Flash;
    use Password\Validator;
    use App\QueryBuilder;

    class AuthController{
        private $auth;
        private $validator;
        private $queryBuilder;

        public function __construct(Auth $auth, Validator $validator, QueryBuilder $queryBuilder){
            $this->auth = $auth;
            $this->validator = $validator;
            $this->queryBuilder = $queryBuilder;
        }


        private $selector;
        private $token;
        public function registration(){
            $this->validator->setMinLength(7);
            $this->validator->setMinNumbers(2);
            $this->validator->setMinUpperCaseLetters(2);
            if (!$this->validator->isValid($_POST['password'])) {
                Flash::set_one('error', 'Пароль должен содержать минимум 7 символов, из которых 2 цифры и 2 буквы в верхнем регистре!');
                Header::redirect('/register');
            }
            try {
                $userId = $this->auth->register($_POST['email'], $_POST['password'], $_POST['username'], function ($selector, $token) {
                    $this->selector = $selector;
                    $this->token = $token;
                });
                $selector = $this->selector;
                $token = $this->token;
                Header::redirect("/verification?selector=$selector&token=$token&id=$userId");
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
            catch (\Delight\Auth\TooManyRequestsException $e) {
                Flash::set_one('error', 'Слишком много попыток!');
            }
            Header::redirect('/register');
        }

        public function verification(){
            try {
                $this->auth->confirmEmail($_GET['selector'], $_GET['token']);
                $this->queryBuilder->insert('users_information', ['user_id' => $_GET['id']]);
                Flash::set_one('success', 'Регистрация успешна!');
                Header::redirect("/login");
            }
            catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
                die('Invalid token');
            }
            catch (\Delight\Auth\TokenExpiredException $e) {
                die('Token expired');
            }
            catch (\Delight\Auth\UserAlreadyExistsException $e) {
                die('Email address already exists');
            }
            catch (\Delight\Auth\TooManyRequestsException $e) {
                die('Too many requests');
            }
        }

        public function auth(){//авторизация
            try {
                $this->auth->login($_POST['email'], $_POST['password']);
                Header::redirect("/users");
            }
            catch (\Delight\Auth\InvalidEmailException $e) {
                Flash::set_one('error', 'Неверный email!');
            }
            catch (\Delight\Auth\InvalidPasswordException $e) {
                Flash::set_one('error', 'Неверный пароль!');
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                Flash::set_one('error', 'Email не подтверждён!');
            }
            catch (\Delight\Auth\TooManyRequestsException $e) {
                Flash::set_one('error', 'Слишком много попыток!');
            }
            Header::redirect("/login");
        }

        public function logout(){
            $this->auth->logOut();
            Header::redirect("/login");
        }

        public function test(){
            try {
                $this->auth->admin()->logInAsUserById(15);
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown ID');
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                die('Email address not verified');
            }
            Header::redirect("/");
        }
    }

?>
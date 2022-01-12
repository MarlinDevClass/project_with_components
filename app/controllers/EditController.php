<?php 
    namespace App\Controllers;
    use App\Header;
    use Delight\Auth\Auth;
    use Password\Validator;
    use App\QueryBuilder;
    use App\File;
    use App\Flash;

    class EditController{
        private $auth;
        private $queryBuilder;
        private $file;
        private $validator;
        private $temp;

        public function __construct(Auth $auth, QueryBuilder $queryBuilder, File $file, Validator $validator){
            $this->auth = $auth;
            $this->queryBuilder = $queryBuilder;
            $this->file = $file;
            $this->validator = $validator;
        }

        public function edit($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $this->queryBuilder->update('users_information', [
                'name' => $_POST['name'],
                 'position' => $_POST['position'],
                 'phone' => $_POST['phone'],
                 'address' => $_POST['address']],
                [['user_id = :user_id'], ['user_id' => $id]]
            );
            Flash::set_one('success', 'Пользователь успешно обновлён!');
            Header::redirect('/users');
        }
    
        public function status($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $this->queryBuilder->update('users_information', ['status' => $_POST['status']], [['user_id = :user_id'], ['user_id' => $id]]);
            Flash::set_one('success', 'Статус успешно изменён!');
            Header::redirect('/users');
        }

        public function image($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $user = $this->queryBuilder->selectDefinite('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            $this->file->upload($id, $_FILES['image'], $user['img'], 'users_information', 'img/demo/avatars/');
            Flash::set_one('success', 'Аватарка успешно изменена!');
            Header::redirect('/users');
        }

        public function email($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $this->temp = $this->auth->getUserId();//запоминаем id пользователя, под которым авторизованы в данный момент
            //авторизовываемся под пользователем, которому меняем email
            try {
                $this->auth->admin()->logInAsUserById($id);
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown ID');
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                die('Email address not verified');
            }
            //меняем email и редиректим на обработчик подтверждения
            try {
                $this->auth->changeEmail($_POST['email'], function ($selector, $token) {
                    Header::redirect("/verification-edit?selector=$selector&token=$token&id=$this->temp");
                });
            }
            catch (\Delight\Auth\InvalidEmailException $e) {
                Flash::set_one('error', 'Не действительный email!');
            }
            catch (\Delight\Auth\UserAlreadyExistsException $e) {
                Flash::set_one('error', 'Email уже существует!');
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                die('Account not verified');
            }
            catch (\Delight\Auth\NotLoggedInException $e) {
                die('Not logged in');
            }
            catch (\Delight\Auth\TooManyRequestsException $e) {
                Flash::set_one('error', 'Слишком много попыток изменить email!');
            }
            //снова заходим под первым пользователем
            try {
                $this->auth->admin()->logInAsUserById($this->temp);
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown ID');
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                die('Email address not verified');
            }
            Header::redirect("/security/$id");
        }

        public function verification(){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            try {
                $this->auth->confirmEmail($_GET['selector'], $_GET['token']);
                Flash::set_one('success', 'Email успешно изменён!');
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

            try {//снова заходим в аккаунт, в котором были изначально
                $this->auth->admin()->logInAsUserById($_GET['id']);
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown ID');
            }
            catch (\Delight\Auth\EmailNotVerifiedException $e) {
                die('Email address not verified');
            }

            Header::redirect("/users");
        }

        public function password($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            if($_POST['password'] != $_POST['password_confirmation']){
                Flash::set_one('error', 'Пароли не совпадают!');
                Header::redirect("/security/$id");
            }
            $this->validator->setMinLength(7);
            $this->validator->setMinNumbers(2);
            $this->validator->setMinUpperCaseLetters(2);
            if (!$this->validator->isValid($_POST['password'])) {
                Flash::set_one('error', 'Пароль должен содержать минимум 7 символов, из которых 2 цифры и 2 буквы в верхнем регистре!');
                Header::redirect("/security/$id");
            }
            try {
                $this->auth->admin()->changePasswordForUserById($id, $_POST['password']);
                Flash::set_one('success', 'Пароль успешно изменён!');
                Header::redirect("/users");
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown ID');
            }
            catch (\Delight\Auth\InvalidPasswordException $e) {
                die('Invalid password');
            }
        }

        public function delete($id){
            if (!$this->auth->isLoggedIn()) {
                Header::redirect("/login");
            }
            if (!$this->auth->hasRole(\Delight\Auth\Role::ADMIN) && $this->auth->getUserId() != $id){
                Header::redirect("/");
            }
            $user = $this->queryBuilder->selectDefinite('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            try {
                $this->auth->admin()->deleteUserById($id);
            }
            catch (\Delight\Auth\UnknownIdException $e) {
                die('Unknown ID');
            }
            if($user['img'] != 'avatar-m.png'){
                unlink('img/demo/avatars/'.$user['img']);
            }
            $this->queryBuilder->delete('users_information', [['user_id = :user_id'], ['user_id' => $id]]);
            Flash::set_one('success', 'Пользователь успешно удалён!');
            if($id == $this->auth->getUserId()){
                $this->auth->logOut();
                Header::redirect("/login");
            }
            Header::redirect("/users");
        }
    }
?>
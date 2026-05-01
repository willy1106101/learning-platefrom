<?php
    require_once 'models/IndexModel.php';
    require_once 'views/IndexView.php';
    session_start();
    class IndexController {
        private $model;

        public function __construct($IndexModel) {
            $this->model = $IndexModel;
        }

        // 已經登入，直接返回管理介面
        public function index() {
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null){
                header("Location: ./student");
                exit;
            }
            if(isset($_SESSION['id']) && $_SESSION['id'] !== null){
                header("Location: ./teacher");
                exit;
            }
            $data = $this->model->getData();
            $view = new IndexView();
            $view->render($data,'student');
        }
        
        // 已經登入，直接返回管理介面
        public function teacher() {
            if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null){
                header("Location: ./student");
                exit;
            }
            if(isset($_SESSION['id']) && $_SESSION['id'] !== null){
                header("Location: ./teacher");
                exit;
            }
            $data = $this->model->getData();
            $view = new IndexView();
            $view->render($data,'teacher');
        }

        // 登入驗證(學生)
        public function Student_login($username, $password) {
            $user = $this->model->findUserByStudentUsername($username); 
            if ($user === null) {
                echo "<h1 class='text-center text-danger'>查無資料!請重新登入~<h1>";
                echo "<script>setInterval(function(){location.href='./index';},1000);</script>";
                return;
                
            }
            if ($username && $password === $user->name) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['stdId'] = $user->stdId;
                $_SESSION['name'] = $user -> name;
                header("Location: ./student");
                exit;
            } else {
                echo "<h1 class='text-center text-danger'>學號姓名錯誤!請重新登入~<h1>";
                echo "<script>setInterval(function(){location.href='./index';},1000);</script>";
                return;
            }
        }

        // 登入驗證(老師)
        public function Teacher_login($username, $password) {
            $user = $this->model->findUserByTeacherUsername($username);
            if ($user === null) {
                echo "<h1 class='text-center text-danger'>查無資料!請重新登入~<h1>";
                echo "<script>setInterval(function(){location.href='./index?t=teacher';},1000);</script>";
                return;
            }
            if ($user && $password === $user->password) {
                $_SESSION['id'] = $user->id;
                $_SESSION['teacher'] = $user->username;
                $_SESSION['password'] = $user -> password;
                header("Location: ./teacher");
                exit;
            } else {
                echo "<h1 class='text-center text-danger'>帳號密碼錯誤!請重新登入~<h1>";
                echo "<script>setInterval(function(){location.href='./index?t=teacher';},1000);</script>";
                return;
            }
        }

        // 登出
        public function logout() {
            if(isset($_SESSION['id'])){
                session_destroy();
                header("Location: ./index?t=teacher");
                exit;
            }

            if(isset($_SESSION['user_id'])){
                session_destroy();
                header("Location: ./index");
                exit;
            }
        }
    }
?>

<?php
    require_once 'models/StudentModel.php';
    require_once 'views/StudentView.php';
    session_start();
    class StudentController {
        private $model;

        public function __construct($StudentModel) {
            $this->model = $StudentModel;
        }

        // 頁面初始化
        public function index() {
            if (!isset($_SESSION['user_id'])) {
                header("Location: ./index");
                exit;
            }

            $data = $this->model->getUserData($_SESSION['user_id']);

            $ShowExamQuetypeList = $this->model->getshowexamquetypeData();
            $ShowExamList = $this->model->showExamList();

            $view = new StudentView();
            $view->render($data,$ShowExamList,$ShowExamQuetypeList);
        }

        // 登出
        public function logout() {
            session_destroy();
            header("Location: ./index");
            exit;
        }
    }
?>
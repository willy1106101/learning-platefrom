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

            // 1. 取得基本資料與清單
            $data = $this->model->getUserData($_SESSION['user_id']);
            $ShowExamQuetypeList = $this->model->getshowexamquetypeData();
            $ShowExamList = $this->model->showExamList();

            // 2. 【新增】抓取每一筆考試的分析報告
            $allReports = [];
            if (!empty($ShowExamList)) {
                foreach ($ShowExamList as $exam) {
                    $eid = $exam['exam_id'];
                    // 呼叫單場分析方法，並將結果存入以 exam_id 為索引的陣列
                    $allReports[$eid] = $this->model->getSingleExamReport($_SESSION['user_id'], $eid);
                }
            }

            // 3. 渲染視圖
            $view = new StudentView();
            // 將 $allReports 傳入 View
            $view->render($data, $ShowExamList, $ShowExamQuetypeList, $allReports);
        }

        // 登出
        public function logout() {
            session_destroy();
            header("Location: ./index");
            exit;
        }
    }
?>
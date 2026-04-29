<?php
    require_once 'models/TeacherModel.php';
    require_once 'views/TeacherView.php';
    session_start();
    class TeacherController {
        private $model;

        public function __construct($TeacherModel) {
            $this->model = $TeacherModel;
        }

        // 頁面初始化
        public function index() {
            if (!isset($_SESSION['id'])) {
                header("Location: ./index");
                exit;
            }
            $view = new TeacherView();

            $data = $this->model->getUserData($_SESSION['id']);
            $ShowExamList = $this->model->showExamList($_GET['p']??'');
            if (isset($_GET['classid'])) {
                $editclassData = $this->model->geteditclassData($_GET['classid']);
                return $view -> render($data,$ShowExamList,$editclassData,'');
            }

            if (isset($_GET['examid'])) {
                $editexamData = $this->model->geteditexamData($_GET['examid']);
                $showexamclassData =  $this->model->getshowexamclassData();
                return $view -> render($data,$ShowExamList,$editexamData,$showexamclassData);
            }
            
            

            if (isset($_GET['studentid'])) {
                $editstudentData = $this->model->geteditstudentData($_GET['studentid']);
                $showstudentclassData =  $this->model->getshowexamclassData();
                return $view -> render($data,$ShowExamList,$editstudentData,$showstudentclassData);
            }

            if (isset($_GET['p']) && $_GET['p'] === 'question') {
                $questionSets = $this->model->getQuestionSet();
                $examData = [];

                // 將大題、小題和選項組裝成巢狀資料結構
                foreach ($questionSets as $questionSet) {
                    $questionSetId = $questionSet['id'];
                    $questions = $this->model->getQuestions($questionSetId);
                    $questionsData = [];

                    foreach ($questions as $question) {
                        $questionId = $question['id'];
                        $options = $this->model->getOptions($questionId);

                        $questionsData[] = [
                            'question' => $question,
                            'options' => $options,
                        ];
                    }

                    $examData[] = [
                        'questionSet' => $questionSet,
                        'questions' => $questionsData,
                    ];
                }
                return $view->render($data,$examData,'','');
            }

            if (isset($_GET['p']) && $_GET['p'] === 'add_student') {
                $showexamclassData =  $this->model->getshowexamclassData();
                return $view -> render($data,$ShowExamList,'',$showexamclassData);
            }

            $view->render($data,$ShowExamList,'','');
        }
        
        // 班級
        public function editclassData($classname,$classid){
            $this ->model->classedit($classname,$classid);
        }

        public function addclassData($classname){
            $this ->model->classadd($classname);
        }

        public function delclassData($classid){
            $this ->model->classdel($classid);
        }

        // 學生
        public function editstudentData($stdId, $name, $classid, $id){
            $this ->model->studentedit($stdId, $name, $classid, $id);
        }

        public function addstudentData($stdId,$name,$classid){
            $this ->model->studentadd($stdId,$name,$classid);
        }

        public function addstudentxlsxData($data){
            if (empty($data)) {
                return "<script>alert('加入學生資料失敗!');</script>";
            }
            $successCount = 0;
            $failedRows = [];

            foreach ($data as $index => $row) {
                $stdId = trim($row["stdId"] ?? '');
                $classid = trim($row['classid'] ?? '');
                $name = trim($row['name'] ?? '');

                if (!empty($stdId) && !empty($classid) && !empty($name)) {
                    $result = $this->model->studentxlsxadd($stdId, $name, $classid);
                    if ($result) {
                        $successCount++;
                    } else {
                        $failedRows[] = ['index' => $index, 'data' => $row];
                    }
                } else {
                    $failedRows[] = ['index' => $index, 'data' => $row, 'error' => '缺少必要欄位'];
                }
            }
            if($successCount === count($data)){
                echo "加入學生資料成功!";
            }else{
                echo "加入學生資料失敗!<br>failed_rows:".$failedRows;
            }
        }

        public function delstudentData($id){
            $this ->model->studentdel($id);
        }

        public function editteacherData($username, $password, $id){
            $this ->model->teacheredit($username, $password, $id);
        }

        // 登出
        public function logout() {
            session_destroy();
            header("Location: ./index");
            exit;
        }
    }
?>
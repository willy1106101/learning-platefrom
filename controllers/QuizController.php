<?php
    require_once 'models/QuizModel.php';
    require_once 'views/QuizView.php';
    session_start();
    class QuizController {
        private $model;

        public function __construct($QuizModel) {
            $this->model = $QuizModel;
        }

        // 頁面初始化
        public function index() {
            if (!isset($_SESSION['user_id'])) {
                header("Location: ./index");
                exit;
            }
            $data = $this->model->getUserData($_SESSION['user_id']);
            $getIsExamQue = $this ->model->getIsExamQue($_GET['examid']??null,$_SESSION['reexamid']??null,$_SESSION['error_questions']??null,$_SESSION['renewtest']??null,$_SESSION['quenum']??'1');
            $questionSets = $this->model->getQuestionSet($_SESSION['errorqueid']??null,$getIsExamQue['error_questions_json'],$getIsExamQue['setting_question_num'],$_SESSION['quetype']??null);
            $examData = [];
            
            // 將大題、小題和選項組裝成巢狀資料結構
            foreach ($questionSets['result'] as $questionSet) {
                $questionSetId = $questionSet['id'];
                $questions = $this->model->getQuestions($questionSetId,$_SESSION['errorqueid']??null,$questionSets['data'],$questionSets['errorqueIds']);
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
            $view = new QuizView();
            $view->render($data,$examData,$getIsExamQue['question_ids'],$_SESSION['quetype']??null);
        }

        public function checkexamanswer($answer){
            $user_id = $_SESSION['user_id'];
            $this->model->check_exam_answer($user_id,$answer);
        }
        
        public function requiz($r,$examid,$lastque,$questionnum,$quetype){
            $this->model->requiz($r,$examid,$lastque,$questionnum,$quetype);
        }

        // 登出
        public function logout() {
            session_destroy();
            header("Location: ./index");
            exit;
        }
    }
?>
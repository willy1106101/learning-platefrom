<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>學習平台</title>
        <meta name="version" content="v2026.04.2901">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
    <?php
        require_once 'config/db.php';

        spl_autoload_register(function ($class_name) {
            $paths = ['controllers/', 'models/', 'views/'];
            foreach ($paths as $path) {
                $file = $path . $class_name . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });

        $url = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'index';

        function createController($controllerName, $model) {
            $controllerClass = $controllerName . 'Controller';
            if (!class_exists($controllerClass)) {
                http_response_code(404);
                echo "404 not found";
                exit();
            }
            return new $controllerClass($model);
        }

        try {
            switch ($url) {
                case 'student_login_check':
                    $IndexModel = getModel('Index');
                    $controller = createController('Index', $IndexModel);
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->Student_login($_POST['username']??'', $_POST['password']??'');
                    } else {
                        $controller->index();
                    }
                    break;
                case 'teacher_login_check':
                    $IndexModel = getModel('Index');
                    $controller = createController('Index', $IndexModel);
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->Teacher_login($_POST['username']??'',$_POST['password']??'');
                    } else {
                        $controller->teacher();
                    }
                    break;
                // 班級
                case 'save_edit_class':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->editclassData($_POST['classname']??'',$_POST['classid']??'');
                    }
                    break;
                case 'save_add_class':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->addclassData($_POST['classname']??'');
                    }
                    break;
                case 'del_class':
                    $TeacherModel = getModel('Teacher');
                    $controller = createController('Teacher', $TeacherModel);
                    $controller->delclassData($_GET['classid']??'');
                    break;
                // 考試
                case 'save_edit_exam':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->editexamData($_POST['examid']??'',$_POST['class_id']??'',$_POST['startTime']??'',$_POST['endTime']??'',$_POST['total_questions']??'',$_POST['totalscore']??'',$_POST['is_open']??'');
                    }
                    break;
                case 'save_add_exam':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->addexamData($_POST['class_id']??'',$_POST['startTime']??'',$_POST['endTime']??'',$_POST['total_questions']??'',$_POST['totalscore']??'',$_POST['is_open']??'');
                    }
                    break;
                case 'del_exam':
                    $TeacherModel = getModel('Teacher');
                    $controller = createController('Teacher', $TeacherModel);
                    $controller->delexamData($_GET['examid']??'');
                    break;
                case 'save_edit_teacher':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->editteacherData($_POST['username']??'',$_POST['password']??'',$_POST['id']??'');
                    }
                    break;
                // 學生
                case 'save_edit_student':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->editstudentData($_POST['stdId']??'',$_POST['name']??'',$_POST['classid']??'',$_POST['id']??'');
                    }
                    break;
                case 'save_add_student':
                    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['x']) && $_POST['x'] === 'xlsx'){

                        // 獲取 POST 資料
                        $postData = json_decode($_POST['data'], true);

                        if (!$postData || !isset($postData['sheet'])) {
                            echo json_encode(['message' => '資料接收失敗', 'error' => true]);
                            exit;
                        }
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->addstudentxlsxData($postData['sheet']);
                    }else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $TeacherModel = getModel('Teacher');
                        $controller = createController('Teacher', $TeacherModel);
                        $controller->addstudentData($_POST['stdId']??'',$_POST['name']??'',$_POST['classid']??'');
                    }
                    break;
                case 'del_student':
                    $TeacherModel = getModel('Teacher');
                    $controller = createController('Teacher', $TeacherModel);
                    $controller->delstudentData($_GET['studentid']??'');
                    break;
                // 檢查考卷，送出分數
                case 'check_exam_answer':
                    $checkexamanswerModel =getModel('Quiz');
                    $controller = createController('Quiz',$checkexamanswerModel);
                    $controller ->checkexamanswer($_POST['answers']);
                    break;
                case 'requiz':
                    $checkexamanswerModel =getModel('Quiz');
                    $controller = createController('Quiz',$checkexamanswerModel);
                    $controller ->requiz($_GET['r']??null,$_GET['examid']??null,$_GET['lastque']??null,$_POST['questionnum']??null,$_POST['quetype']??null);
                    break;
                // 考試頁面
                case 'quiz':
                case 'Quiz':
                    $StudentModel = getModel('Quiz');
                    $controller = createController('Quiz', $StudentModel);
                    $controller->index();
                    break;
                // 學生頁面
                case 'student':
                case 'Student':
                    $StudentModel = getModel('Student');
                    $controller = createController('Student', $StudentModel);
                    $controller->index();
                    break;
                // 老師頁面
                case 'teacher':
                case 'Teacher':
                    $TeacherModel = getModel('Teacher');
                    $controller = createController('Teacher', $TeacherModel);
                    $controller->index();
                    break;
                // 老師頁面
                case 'dev':
                case 'Dev':
                    $DevModel = getModel('Dev');
                    $controller = createController('Dev', $DevModel);
                    $controller->index();
                    break;
                // 登出
                case 'Logout':
                case 'logout':
                    $IndexModel = getModel('Index');
                    $controller = createController('Index', $IndexModel);
                    $controller->logout();
                    break;
                // 登入頁面
                case 'index':
                case 'Index':
                default:
                    $IndexModel = getModel('Index');
                    $controller = createController('Index', $IndexModel);
                    if(isset($_GET['t']) && $_GET['t'] === 'teacher'){
                        $controller->teacher();
                    }else{
                        $controller->index();
                    }
                    break;
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo "Internal Server Error: " . $e->getMessage();
        }

        function getModel($name) {
            global $host, $username, $password, $dbname;
            $modelClass = ucfirst($name) . 'Model';
            if (class_exists($modelClass)) {
                return new $modelClass($host, $username, $password, $dbname);
            }
            throw new Exception("Model $modelClass not found.");
        }
    ?>
    </body>
</html>

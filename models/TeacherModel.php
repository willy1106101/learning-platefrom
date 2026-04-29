<?php
class TeacherModel {
    private $db;

    public function __construct($host, $username, $password, $dbname) {
        // 初始化資料庫連接
        $this->db = new mysqli($host, $username, $password, $dbname);
        $this->db->set_charset("utf8mb4");
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    // 顯示修改、加入資料-班級(考試)
    public function getshowexamclassData() {
        $query = "SELECT * FROM stdclass";
        $stmt = $this->db->query($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->db->error);
        }

        $result = $stmt;
        if ($result->num_rows <= 0) {
            return null; 
        }

        $ExamList = [];
        while ($row = $result->fetch_assoc()) {
            $ExamList[] = $row;
        }

        $result->free();

        return $ExamList;
    }

    // 顯示老師資料
    public function getUserData($userId) {
        $query = "SELECT * FROM teacher WHERE id = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null; 
        }

        $user = $result->fetch_assoc(); 
        $stmt->close(); 
        return $user;
    }

    public function showExamList($p){
        if(isset($p)&& $p ==='student'){
            $query = "SELECT students.*, stdclass.classname FROM students
            LEFT JOIN stdclass ON students.class_id = stdclass.classid
            ORDER BY students.id ASC";
        }else if(isset($p)&& $p ==='class'){
            $query = "SELECT * FROM stdclass";
        }else{
            $query = "SELECT stdscores.*, students.*,stdclass.* FROM stdscores
                    JOIN students ON stdscores.student_id = students.id
                    JOIN stdclass ON stdclass.classid = students.class_id
                    WHERE CHAR_LENGTH(stdscores.exam_id) = 8
                    ORDER BY stdscores.id DESC";
        }
        $result = $this->db->query($query);

        if (!$result) {
            throw new Exception("Query failed: " . $this->db->error);
        }

        if ($result->num_rows === 0) {
            return null; 
        }

        $ExamList = [];
        while ($row = $result->fetch_assoc()) {
            $ExamList[] = $row;
        }

        $result->free();

        return $ExamList;

    }

    // 顯示修改資料(班級)
    public function geteditclassData($classid) {
        $query = "SELECT * FROM stdclass WHERE classid = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $classid);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null; 
        }

        $class = $result->fetch_assoc(); 
        $stmt->close(); 
        return $class;
    }

    // 顯示修改資料(學生)
    public function geteditstudentData($studentid) {
        $query = "SELECT * FROM students WHERE id = ?";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $studentid);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return null; 
        }

        $class = $result->fetch_assoc(); 
        $stmt->close(); 
        return $class;
    }

    // 加入資料(班級)
    public function classadd($classname){
        $examid = 0;
        $isUnique = false;

        // 生成隨機 ID 並檢查是否已存在
        while (!$isUnique) {
            $classid = rand(100000, 999999); // 生成 6 位數隨機 classid
            $sqlCheck = "SELECT classid FROM stdclass WHERE classid = ?";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bind_param("i", $classid);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();

            if ($resultCheck->num_rows == 0) {
                $isUnique = true; // 如果資料庫中沒有這個 classid，則認為是唯一的
            }
        }

        
        $sql = "INSERT INTO `stdclass` (`classid`,`classname`) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $classid, $classname);

        if ($stmt->execute()) {
            echo "<script>alert('班級創建成功'); location.href='./teacher?p=class';</script>";
        } else {
            echo "<script>alert('班級創建失敗');</script>";
        }
    }


    // 加入資料(學生)
    public function studentadd($stdId,$name,$classid){

        $sql = "INSERT INTO students (`stdId`, `name`,`class_id`) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isi", $stdId, $name,$classid);

        if ($stmt->execute()) {
            echo "<script>alert('學生資料已成功加入'); location.href='./teacher?p=student';</script>";
        } else {
            echo "<script>alert('加入學生資料失敗');</script>";
        }
    }

    public function studentxlsxadd($stdId, $name,$classid){
        $sql = "INSERT INTO students (`stdId`, `name`,`class_id`) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("isi", $stdId, $name,$classid);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // 修改資料(班級)
    public function classedit($classname,$classid){

        $sql = "UPDATE stdclass SET classname = ? WHERE classid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $classname, $classid);

        if ($stmt->execute()) {
            echo "<script>alert('班級已更新'); location.href='./teacher?p=class';</script>";
        } else {
            echo "<script>alert('班級更新失敗');</script>";
        }
    }


    // 修改資料(學生)
    public function studentedit($stdId, $name, $classid, $id){

        $sql = "UPDATE students SET `stdId` = ?, `name` = ?,`class_id` = ? WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("issi", $stdId, $name, $classid, $id);

        if ($stmt->execute()) {
            echo "<script>alert('學生更新成功'); location.href='./teacher?p=student';</script>";
        } else {
            echo "<script>alert('學生更新失敗');</script>";
        }
    }

    // 修改資料(老師)
    public function teacheredit($username, $password, $id){

        $sql = "UPDATE teacher SET `username` = ?, `password`= ? WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $username, $password, $id);

        if ($stmt->execute()) {
            echo "<script>alert('老師更新成功'); location.href='./teacher?p=edit_own';</script>";
        } else {
            echo "<script>alert('老師更新失敗');</script>";
        }
    }

    // 刪除資料(班級)
    public function classdel($classid){

        $sql = "DELETE FROM `stdclass` WHERE `classid` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $classid); // 使用整數型別綁定參數

        if ($stmt->execute()) {
            echo "<script>alert('班級刪除成功'); location.href='./teacher?p=class';</script>";
        } else {
            echo "<script>alert('班級刪除失敗');</script>";
        }
    }

    // 刪除資料(學生)
    public function studentdel($id){

        $sql = "DELETE FROM `students` WHERE `id` = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "<script>alert('學生刪除成功'); location.href='./teacher?p=student';</script>";
        } else {
            echo "<script>alert('學生刪除失敗');</script>";
        }
    }

    // 顯示大題
    public function getQuestionSet(){
        $sql = "SELECT id, content, image_url,que_type FROM questionsets ORDER BY id";
        return $this->db->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    // 顯示小題
    public function getQuestions($questionSet=null) {
        $questionSql = "SELECT id, question_text, correct_option FROM questions WHERE question_set_id = " . $questionSet . " ORDER BY id";
        return $this->db->query($questionSql)->fetch_all(MYSQLI_ASSOC);
    }

    // 顯示選項
    public function getOptions($question) {
        $optionSql = "SELECT option_letter, option_text, option_image FROM options WHERE question_id = " . $question . " ORDER BY id";
        return $this->db->query($optionSql)->fetch_all(MYSQLI_ASSOC);
    }

    public function __destruct() {
        // 關閉資料庫連接
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>


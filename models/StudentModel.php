<?php
class StudentModel {
    private $db;

    public function __construct($host, $username, $password, $dbname) {
        // 初始化資料庫連接
        $this->db = new mysqli($host, $username, $password, $dbname);
        $this->db->set_charset("utf8mb4");
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    // 顯示學生資料
    public function getUserData($userId) {
        $query = "SELECT * FROM students WHERE id = ?";
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

    // 顯示資料(章節)
    public function getshowexamquetypeData() {
        $query = "SELECT * FROM question_type";
        $stmt = $this->db->query($query);

        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $this->db->error);
        }

        $result = $stmt;
        if ($result->num_rows <= 0) {
            return null; 
        }

        $quetypeList = [];
        while ($row = $result->fetch_assoc()) {
            $quetypeList[] = $row;
        }

        $result->free();

        return $quetypeList;
    }

    //  顯示當前考場列表(學生)
    public function showExamList(){
        $query = "SELECT exam_id, score,quiztime FROM stdscores
        WHERE CHAR_LENGTH(exam_id) = 8 AND student_id = ".$_SESSION['user_id']."
        ORDER BY id DESC";
        
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

    public function __destruct() {
        // 關閉資料庫連接
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>


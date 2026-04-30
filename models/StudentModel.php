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

    public function getSingleExamReport($student_id, $exam_id) {
        $stmt = $this->db->prepare("SELECT answer, questionset_id, quiztime FROM stdscores WHERE student_id = ? AND exam_id = ? LIMIT 1");
        $stmt->bind_param("ss", $student_id, $exam_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) return null;

        $totalQ = 0;
        $wrongQ = 0;

        // --- 修正後的解析邏輯 ---
        $rawAnswer = $row['answer'];

        // 如果結尾是 ,[]，代表後面還有空的（對的）資料，我們把它補齊成標準格式
        // 例如將 {"q.1":...},[] 轉換成 [{"q.1":...},[]]
        $jsonForDecode = '[' . str_replace(',[]', ',{}', $rawAnswer) . ']'; 
        $decodedAns = json_decode($jsonForDecode, true);
        
        if (is_array($decodedAns)) {
            foreach ($decodedAns as $item) {
                if (empty($item)) continue;
                
                // 遍歷每一個題組 (如 q.112)
                foreach ($item as $qSet) {
                    // 這裡 count($qSet) 就會包含該題組內所有的題目（不論對錯）
                    $totalQ += is_array($qSet) ? count($qSet) : 0;
                }
            }
        }

        // 解析錯題數 (維持從 questionset_id 抓取，因為這裡紀錄的是確定錯的編號)
        $decodedWrong = json_decode($row['questionset_id'], true) ?: [];
        if (is_array($decodedWrong)) {
            foreach ($decodedWrong as $qids) {
                $wrongQ += count((array)$qids);
            }
        }

        return [
            'total' => $totalQ,
            'wrong' => $wrongQ,
            'rate'  => ($totalQ > 0) ? round(($wrongQ / $totalQ) * 100, 1) : 0,
            'time'  => $row['quiztime']
        ];
    }

    public function __destruct() {
        // 關閉資料庫連接
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>


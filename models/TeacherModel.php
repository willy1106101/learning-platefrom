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


    public function getExamBasicInfo($examid) {
        $questionset_id = '';
        $error_questions_json = null;
        
        // 1. 使用預處理語句確保安全
        $stmt = $this->db->prepare("SELECT answer, questionset_id,student_id FROM stdscores WHERE exam_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $examid);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $raw_answer = $row['answer'] ?? ''; 
                $questionset_id = $row['questionset_id'] ?? '';
                $studentData =  $row['student_id']??'';
                // 2. 解析 JSON (補上中括號以符合您資料庫的存儲格式)
                if ($raw_answer !== '') {
                    // 依照您目前的資料結構，將殘缺的 JSON 補上中括號後解析
                    $parsed = json_decode('[' . $raw_answer . ']', true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $error_questions_json = $parsed;
                    }
                }
            }
            $stmt->close();
        }

        // 3. 【新增】將錯題資料輸出給前端 JavaScript
        // 這樣 View 裡的標示邏輯才能抓到 errorQuestions 變數
        $js_output_data = $error_questions_json ?? []; 
        echo '<script>const errorQuestions = ' . json_encode($js_output_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';

        return [
            'questionset_id' => $questionset_id,
            'error_questions_json' => $error_questions_json,
            'student_id' => $studentData
        ];
    }

    /**
     * 2. 取得大題列表 (Questionsets)
     */
    public function getQuestionSetsByJson($error_questions_json) {
        if (!is_array($error_questions_json)) return [];

        $targetSetIds = [];
        foreach ($error_questions_json as $item) {
            foreach ($item as $key => $val) {
                // 清除 "q." 取得數字 ID
                $cleanId = intval(str_replace('q.', '', (string)$key));
                if ($cleanId > 0) $targetSetIds[] = $cleanId;
            }
        }
        $targetSetIds = array_unique($targetSetIds);

        if (empty($targetSetIds)) return [];

        $placeholders = implode(',', array_fill(0, count($targetSetIds), '?'));
        $sql = "SELECT id, content, image_url FROM questionsets WHERE id IN ($placeholders) ORDER BY FIELD(id, $placeholders)";
        
        $stmt = $this->db->prepare($sql);
        // 傳入兩次 $targetSetIds 為了 FIELD 排序，確保順序正確
        $params = array_merge($targetSetIds, $targetSetIds);
        $types = str_repeat('i', count($params));

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * 3. 取得指定大題內「有紀錄在 JSON 裡」的小題
     */
    public function getQuestionsByJson($setId, $error_questions_json) {
        $cleanSetId = intval($setId);
        $targetQueIds = [];

        foreach ($error_questions_json as $item) {
            foreach ($item as $qKey => $qVal) {
                // 檢查是否為當前處理的大題
                if (intval(str_replace('q.', '', (string)$qKey)) === $cleanSetId) {
                    if (is_array($qVal)) {
                        foreach ($qVal as $queId => $info) {
                            $targetQueIds[] = intval($queId);
                        }
                    }
                }
            }
        }

        if (empty($targetQueIds)) return [];

        $placeholders = implode(',', array_fill(0, count($targetQueIds), '?'));
        $sql = "SELECT * FROM questions WHERE question_set_id = ? AND id IN ($placeholders) ORDER BY id";
        
        $stmt = $this->db->prepare($sql);
        $params = array_merge([$cleanSetId], $targetQueIds);
        $types = str_repeat('i', count($params));

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    /**
     * 4. 取得選項
     */
    public function getOptions($questionId) {
        // 錯誤原因：bind_param 不接受直接傳入函式處理後的「值」
        // 解決方法：先將處理後的 ID 存入一個變數
        $cleanQuestionId = intval($questionId);

        $stmt = $this->db->prepare("SELECT option_letter, option_text, option_image FROM options WHERE question_id = ? ORDER BY id");
        
        if ($stmt) {
            // 現在傳入的是 $cleanQuestionId 變數，這樣就不會報錯了
            $stmt->bind_param("i", $cleanQuestionId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $result;
        }
        
        return [];
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


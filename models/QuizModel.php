<?php
class QuizModel {
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

    public function getAllWrongQuestionsForExam($student_id,$que_type,$limit_que_num) {
        if (empty($student_id)) {
            return [];
        }
        /* =========================
        * 1️⃣ 撈出所有 questionset_id
        * ========================= */
        $stmt = $this->db->prepare(
            "SELECT questionset_id 
            FROM stdscores 
            WHERE student_id = ?
            AND questionset_id IS NOT NULL
            AND questionset_id != ''
            LIMIT ?"
             
        );
        $stmt->bind_param("ss", $student_id,$limit_que_num);
        $stmt->execute();
        $result = $stmt->get_result();

        $setMap = [];   // questionset_id => [question_id...]

        while ($row = $result->fetch_assoc()) {
            $decoded = json_decode($row['questionset_id'], true);

            if (!is_array($decoded)) continue;

            foreach ($decoded as $setId => $qids) {
                if (!isset($setMap[$setId])) {
                    $setMap[$setId] = [];
                }
                foreach ((array)$qids as $qid) {
                    $setMap[$setId][] = (int)$qid;
                }
            }
        }
        $stmt->close();

        if (empty($setMap)) {
            return [];
        }

        /* =========================
        * 2️⃣ 撈題組資料
        * ========================= */
        $setIds = array_keys($setMap);
        $inSet  = implode(',', array_fill(0, count($setIds), '?'));

        $stmt = $this->db->prepare(
            "SELECT id, content, image_url, que_type
            FROM questionsets
            WHERE id IN ($inSet) AND que_type = ?"
        );
        $params = array_merge($setIds, [(int)$que_type]);
        $types = str_repeat('i', count($setIds)) . 'i'; 

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $questionSets = [];
        while ($row = $result->fetch_assoc()) {
            $questionSets[$row['id']] = [
                'questionSet' => [
                    'id'        => (string)$row['id'],
                    'content'   => $row['content'],
                    'image_url' => $row['image_url'],
                    'que_type'  => $row['que_type'],
                ],
                'questions' => []
            ];
        }
        $stmt->close();

        /* =========================
        * 3️⃣ 撈所有錯題 + 選項
        * ========================= */
        $allQids = [];
        foreach ($setMap as $qids) {
            foreach ($qids as $qid) {
                $allQids[] = $qid;
            }
        }
        $allQids = array_values(array_unique($allQids));

        $inQ = implode(',', array_fill(0, count($allQids), '?'));

        $stmt = $this->db->prepare(
            "SELECT 
                q.id AS qid,
                q.question_set_id,
                q.question_text,
                q.correct_option,
                o.option_letter,
                o.option_text,
                o.option_image
            FROM questions q
            LEFT JOIN options o ON o.question_id = q.id
            WHERE q.id IN ($inQ)
            ORDER BY q.question_set_id, q.id"
        );
        $stmt->bind_param(str_repeat('i', count($allQids)), ...$allQids);
        $stmt->execute();
        $result = $stmt->get_result();

        $tempQuestions = [];

        while ($row = $result->fetch_assoc()) {
            $qid = $row['qid'];

            if (!isset($tempQuestions[$qid])) {
                $tempQuestions[$qid] = [
                    'setId'    => $row['question_set_id'],
                    'question' => [
                        'id'             => (string)$qid,
                        'question_text'  => $row['question_text'],
                        'correct_option' => $row['correct_option']
                    ],
                    'options' => []
                ];
            }

            if ($row['option_letter']) {
                $tempQuestions[$qid]['options'][] = [
                    'option_letter' => $row['option_letter'],
                    'option_text'   => $row['option_text'],
                    'option_image'  => $row['option_image']
                ];
            }
        }
        $stmt->close();

        /* =========================
        * 4️⃣ 塞回題組
        * ========================= */
        foreach ($tempQuestions as $q) {
            $setId = $q['setId'];

            if (!isset($questionSets[$setId])) continue;

            $questionSets[$setId]['questions'][] = [
                'question' => $q['question'],
                'options'  => $q['options']
            ];
        }

        /* =========================
        * 5️⃣ 最終輸出
        * ========================= */
        return array_values($questionSets);
    }

    public function getIsExamQue($examid = null, $reexamid = null, $error_questions = null, $renewtest = null, $quenum = null) {
        // 初始化回傳變數
        $questionset_id = '';
        $error_questions_json = null;
        $raw_error_answer = ''; // 用來暫存尚未解析的原始字串

        // ----------------------------------------------------------------
        // 步驟 1：取得原始資料 (合併 $examid 與 $reexamid 的相同邏輯)
        // ----------------------------------------------------------------
        $target_id = $examid ?? $reexamid ?? null;

        if ($target_id !== null) {
            // 使用預處理語句確保 SQL 安全
            $stmt = $this->db->prepare("SELECT answer, questionset_id FROM stdscores WHERE exam_id = ?");
            if ($stmt) {
                $stmt->bind_param("s", $target_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $raw_error_answer = $row['answer'] ?? ''; 
                    $questionset_id = $row['questionset_id'] ?? '';
                }
                $stmt->close();
            }
        } else if (isset($error_questions) && $error_questions !== '') {
            // 直接使用傳入的參數
            $raw_error_answer = $error_questions;
        }

        // ----------------------------------------------------------------
        // 步驟 2：安全地解析 JSON 字串為 PHP 陣列
        // ----------------------------------------------------------------
        if ($raw_error_answer !== '') {
            // 依照您原本的邏輯，將殘缺的 JSON 補上中括號後解析
            $parsed_data = json_decode('[' . $raw_error_answer . ']', true);
            
            // 確保解析成功才賦值，避免 json_decode 失敗回傳 null
            if (json_last_error() === JSON_ERROR_NONE) {
                $error_questions_json = $parsed_data;
            }
        }

        // ----------------------------------------------------------------
        // 步驟 3：安全地輸出給前端 JavaScript (防範 XSS)
        // ----------------------------------------------------------------
        if (empty($renewtest)) {
            // 注意：這裡改為傳入 $error_questions_json (陣列型態)，讓 JS 拿到真正的物件
            // 並加上 JSON_HEX_* 參數徹底防禦 XSS
            $js_output_data = $error_questions_json ?? []; // 若為空則輸出空陣列
            echo '<script>const errorQuestions = ' . json_encode($js_output_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';</script>';
        }

        // ----------------------------------------------------------------
        // 步驟 4：回傳結果
        // ----------------------------------------------------------------
        return [
            'question_ids' => $questionset_id,
            // 【安全防護】將題目數量強制轉為整數，避免後續處理發生型別或注入風險
            'setting_question_num' => isset($quenum) ? intval($quenum) : null,
            'error_questions_json' => $error_questions_json
        ];
    }

    public function getQuestionSet($errorqueid = null, $error_questions_json = null, $setting_question_num = null, $quetype = null) {
        $sql = "";
        $params = [];
        $types = "";
        $data = null;
        $errorqueIds = null;

        // ----------------------------------------------------------------
        // 情境 1: 處理 $errorqueid (錯題再做一次：處理如 {"1085":[1085]} )
        // ----------------------------------------------------------------
        if (isset($errorqueid)) {
            // 確保 $data 是陣列
            $data = is_array($errorqueid) ? $errorqueid : json_decode($errorqueid, true);

            $ids = implode(',', array_map(function($id) {
                return $id;  // 直接返回鍵名，不進行任何處理
            }, array_keys(json_decode($data,true)))); 

            $sql = "SELECT id, content, image_url, que_type FROM questionsets WHERE id IN ($ids) ORDER BY id";
            
        // ----------------------------------------------------------------
        // 情境 2: 處理 $error_questions_json (考後檢閱)
        // ----------------------------------------------------------------
        } else if (isset($error_questions_json)) {
            $data1 = is_array($error_questions_json) ? $error_questions_json : json_decode($error_questions_json, true);
            
            $errorqueIdsq = [];
            $errorqueIds = [];

            if (is_array($data1)) {
                foreach ($data1 as $key => $val) {
                    // 處理陣列包物件結構
                    if (is_int($key) && is_array($val)) {
                        foreach ($val as $subKey => $questions) {
                            $cleanId = intval(str_replace('q.', '', (string)$subKey));
                            if ($cleanId > 0) $errorqueIdsq[] = $cleanId;
                            
                            if (is_array($questions)) {
                                foreach ($questions as $qId => $ans) {
                                    $errorqueIds[] = $qId; 
                                }
                            }
                        }
                    } 
                    // 處理純物件結構
                    else {
                        $cleanId = intval(str_replace('q.', '', (string)$key));
                        if ($cleanId > 0) $errorqueIdsq[] = $cleanId;
                        
                        if (is_array($val)) {
                            foreach ($val as $qId => $ans) {
                                $errorqueIds[] = $qId; 
                            }
                        }
                    }
                }
            }

            $errorqueIdsq = array_unique($errorqueIdsq);
            
            if (empty($errorqueIdsq)) {
                return ['result' => [], 'data' => null, 'errorqueIds' => $errorqueIds];
            }

            $placeholders = implode(',', array_fill(0, count($errorqueIdsq), '?'));
            $sql = "SELECT id, content, image_url, que_type FROM questionsets WHERE id IN ($placeholders) ORDER BY id";
            $params = $errorqueIdsq;
            $types = str_repeat('i', count($errorqueIdsq));

        } else {
            // 情境 3: 一般隨機抽題 (略)
            $limit = intval($setting_question_num);
            if ($limit <= 0) $limit = 10; 

            if (isset($quetype) && $quetype !== '') {
                $sql = "SELECT id, content, image_url, que_type FROM questionsets WHERE que_type = ? ORDER BY RAND() LIMIT ?";
                $params = [$quetype, $limit];
                $types = 'si'; 
            } else {
                $sql = "SELECT id, content, image_url, que_type FROM questionsets ORDER BY RAND() LIMIT ?";
                $params = [$limit];
                $types = 'i';
            }
        }

        // 執行與回傳邏輯
        if (isset($_SESSION['randvalue']) && $_SESSION['randvalue'] !== '') {
            return [
                'result' => $_SESSION['randvalue'],
                'data' => $data ?? null,
                'errorqueIds' => $errorqueIds ?? null
            ];
        } else {
            $stmt = $this->db->prepare($sql);
            if ($stmt === false) die('SQL Prepare Error: ' . $this->db->error);

            if (!empty($params)) $stmt->bind_param($types, ...$params);

            $stmt->execute();
            $resultData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $_SESSION['randvalue'] = $resultData;
            return [
                'result' => $resultData,
                'data' => $data ?? null,
                'errorqueIds' => $errorqueIds ?? null
            ];
        }
    }

    // 顯示小題
    public function getQuestions($questionSet = null, $errorqueid = null, $data = null, $errorqueIds = null) {
        $cleanQuestionSet = intval($questionSet);
        
        $questionSql = "";
        $params = [];
        $types = "";

        // 情境 A：處理 json data 裡的特定題目 ID (錯題再做)
        if (isset($errorqueid) && isset($data)) {
            $decodedData = is_array($data) ? $data : json_decode($data, true);
            if (is_array($decodedData)) {
                foreach ($decodedData as $questionSetId => $questionIds) {
                    
                    // 【關鍵修復】自動清除 "q." 讓大題 ID 能順利對比
                    $cleanSetId = intval(trim($questionSetId, "q."));

                    // 找到對應的題組
                    if ($cleanQuestionSet === $cleanSetId) {
                        if (is_array($questionIds) && !empty($questionIds)) {
                            
                            // 【萬用解析】判斷傳進來的是 [15, 16] 還是 {"15": {"user_answer": "A"}}
                            $extractedIds = [];
                            foreach ($questionIds as $k => $v) {
                                // 如果值是陣列(代表包著答案紀錄)，那就抓它的 Key(題目ID)
                                // 如果值是單純的數字或字串，就代表值本身是題目ID
                                $extractedIds[] = is_array($v) ? $k : $v;
                            }

                            // 強制轉為整數並過濾
                            $cleanIds = array_filter(array_map('intval', $extractedIds));
                            if (empty($cleanIds)) break;

                            $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
                            $questionSql = "SELECT * FROM questions WHERE question_set_id = ? AND id IN ($placeholders) ORDER BY id";
                            
                            $params = array_merge([$cleanQuestionSet], $cleanIds);
                            $types = 'i' . str_repeat('i', count($cleanIds)); 
                            
                            break; 
                        }
                    }
                }
            }

        // 情境 B：直接處理傳入的 $errorqueIds 陣列
        } else if (isset($errorqueIds) && is_array($errorqueIds) && !empty($errorqueIds)) {
            $cleanIds = array_filter(array_map('intval', $errorqueIds));
            
            if (!empty($cleanIds)) {
                $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));
                $questionSql = "SELECT * FROM questions WHERE question_set_id = ? AND id IN ($placeholders) ORDER BY id";
                $params = array_merge([$cleanQuestionSet], $cleanIds);
                $types = 'i' . str_repeat('i', count($cleanIds));
            }

        // 情境 C：預設查詢該題組所有題目
        } else {
            $questionSql = "SELECT id, question_text, correct_option FROM questions WHERE question_set_id = ? ORDER BY id";
            $params = [$cleanQuestionSet];
            $types = "i";
        }

        if (empty($questionSql)) return [];

        $stmt = $this->db->prepare($questionSql);
        if ($stmt === false) die('SQL Prepare Error: ' . $this->db->error);

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    // 顯示選項
    public function getOptions($question) {
        // 【安全防護】將傳入的問題 ID 強制轉為整數
        $cleanQuestionId = intval($question);

        $optionSql = "SELECT option_letter, option_text, option_image FROM options WHERE question_id = ? ORDER BY id";
        
        $stmt = $this->db->prepare($optionSql);
        if ($stmt === false) {
            die('SQL Prepare Error: ' . $this->db->error);
        }

        // 綁定參數 "i" 代表 Integer
        $stmt->bind_param("i", $cleanQuestionId);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $result;
    }

    
    public function check_exam_answer($userId, $std_answer) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            // 【防呆機制】確保有傳入答案，避免後續 count($std_answer) 為 0 導致「除以零 (Division by zero)」崩潰
            if (empty($std_answer) || !is_array($std_answer)) {
                header("Location: ./requiz?r=error");
                exit;
            }

            $error_questions = [];
            $error_questionset = [];
            $correct_questions = [];  // 初始化正確的題目
            $score = 0;

            // 生成一個隨機的 exam_id
            $exam_id = bin2hex(random_bytes(4));

            // ----------------------------------------------------------------
            // 【效能與安全修正】將 Prepare 移到迴圈「外面」
            // ----------------------------------------------------------------
            $sql = "SELECT correct_option, question_set_id FROM questions WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt === false) {
                die('SQL Prepare Error: ' . $this->db->error);
            }

            // 計算分數並收集錯誤的題目
            foreach ($std_answer as $question_id => $user_answer) {
                // 【安全防護】強制確保傳入的問題 ID 是整數
                $clean_q_id = intval($question_id);
                
                // 綁定參數並執行 (i 代表 Integer)
                $stmt->bind_param("i", $clean_q_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $correct_option = $row['correct_option'];
                    $error_questionsetid = $row['question_set_id'];

                    // 比對用戶選的答案和正確答案
                    if ($user_answer != $correct_option) {
                        $error_questions["q." . $error_questionsetid][$clean_q_id] = [
                            'user_answer' => $user_answer,
                            'correct_answer' => $correct_option
                        ];
                        $error_questionset[$error_questionsetid][] = $clean_q_id;
                    } else {
                        $correct_questions["q." . $error_questionsetid][$clean_q_id] = [
                            'c_user_answer' => $user_answer
                        ];
                        $score++; // 增加分數
                    }
                }
            }
            $stmt->close(); // 迴圈結束後再關閉

            // 計算成績百分比
            $percentage_score = ($score / count($std_answer)) * 100;

            // ----------------------------------------------------------------
            // 【邏輯與 JSON 修正】放棄 isset，改用 !empty
            // ----------------------------------------------------------------
            $json_parts = [];
            // 原始程式碼用 isset($correct_questions) 永遠是 true，因為你在上面已經初始化為 [] 了。
            // 必須用 !empty() 判斷裡面是不是真的有資料。
            if (!empty($error_questions)) {
                $json_parts[] = json_encode($error_questions);
            }
            if (!empty($correct_questions)) {
                $json_parts[] = json_encode($correct_questions);
            }
            
            // 為了相容您前端與 getIsExamQue 的讀取邏輯，這裡安全地將兩個 JSON 物件用逗號連接
            // 結果會是 {"q.1":...}, {"q.1":...}
            $error_questions_json = implode(',', $json_parts);
            
            // 處理大題 JSON
            $error_questionsetid_json = !empty($error_questionset) ? json_encode($error_questionset) : '';

            // 將成績和錯題資訊存入 `stdscores` 資料表
            $student_id = $userId;
            
            $insert_sql = "INSERT INTO `stdscores` (`student_id`, `exam_id`, `score`, `answer`, `questionset_id`, `is_quiz`) VALUES (?, ?, ?, ?, ?, 1)";
            $insert_stmt = $this->db->prepare($insert_sql);
            
            // 綁定參數 (s:字串, s:字串, d:浮點數/小數, s:字串, s:字串)
            // 注意分數是百分比小數，所以用 'd' (Double)
            $insert_stmt->bind_param("ssdss", $student_id, $exam_id, $percentage_score, $error_questions_json, $error_questionsetid_json);
            $insert_stmt->execute();
            $insert_stmt->close();

            // 統一存入 Session
            $_SESSION['error_questions'] = $error_questions_json;
            
            // 重新導向回原頁面
            header("Location: ./requiz?r=1");
            // 【關鍵修正】header 跳轉後，必須立刻加上 exit; 阻止伺服器繼續往下執行，這是標準安全規範
            exit; 
        }
    }

    public function requiz($r = null, $examid = null, $lastque = null, $questionnum = null, $quetype = null, $questiontype = null) {
        // 定義一個快速清理 Session 的小陣列，讓程式碼更簡潔好維護
        $keys_to_clear = ['error_questions', 'errorqueid', 'renewtest', 'reexamid', 'quenum', 'quetype', 'randvalue', 'student_id'];

        // ----------------------------------------------------------------
        // 情境 1: 完全重置並回到學生首頁
        // ----------------------------------------------------------------
        if (isset($r) && $r === '1') {
            foreach ($keys_to_clear as $key) unset($_SESSION[$key]);
            header("Location: ./student");
            exit; // 【安全關鍵】跳轉後必須立刻停止執行

        // ----------------------------------------------------------------
        // 情境 2: 重新測驗特定的考卷
        // ----------------------------------------------------------------
        } else if (isset($r) && $r === '2') {
            foreach ($keys_to_clear as $key) unset($_SESSION[$key]);
            
            $_SESSION['renewtest'] = 1;
            // 【安全防護】確保 exam_id 只有英數字 (過濾掉可能的惡意符號)
            $_SESSION['reexamid'] = preg_replace('/[^a-zA-Z0-9]/', '', $examid); 
            
            header("Location: ./quiz");
            exit; 

        // ----------------------------------------------------------------
        // 情境 3: 接續上次的錯題或新設定的測驗
        // ----------------------------------------------------------------
        } else {
            // 處理特定題目的重測 (base64 解碼)
            if (isset($lastque) && $lastque !== '') {
                foreach ($keys_to_clear as $key) unset($_SESSION[$key]);
                
                $decoded_que = base64_decode($lastque);
                // 【關鍵修正】移除 htmlspecialchars，保留完整的 JSON 雙引號格式，讓後續的 json_decode 能正常運作
                $_SESSION['errorqueid'] = $decoded_que !== false ? $decoded_que : null;
                
                header("Location: ./quiz");
                exit; 
                
            // 處理依照數量與題型的新測驗
            } else {
                // 清理除了 randvalue 以外的基礎 Session
                $basic_keys = ['error_questions', 'errorqueid', 'renewtest', 'reexamid', 'quenum', 'quetype'];
                foreach ($basic_keys as $key) unset($_SESSION[$key]);

                $has_valid_input = false;

                if (isset($questionnum) && $questionnum !== '') {
                    // 【安全防護】題數絕對是數字，強制轉為整數
                    $_SESSION['quenum'] = intval($questionnum);
                    $has_valid_input = true;
                }

                if (isset($quetype) && $quetype !== '') {
                    // 【安全防護】過濾題型的特殊字元
                    $_SESSION['quetype'] = htmlspecialchars($quetype, ENT_QUOTES, 'UTF-8');
                    $has_valid_input = true;
                }

                if(isset($questiontype) && $questiontype !== '' && $questiontype == "errnewtest"){
                    $_SESSION['student_id'] = $_SESSION['user_id'];
                    $has_valid_input = true;
                }

                // 【邏輯修正】修復原本會「發送兩次 Location」的嚴重 Bug
                if ($has_valid_input) {
                    header("Location: ./quiz");
                    exit; 
                } else {
                    unset($_SESSION['randvalue']);
                    header("Location: ./student");
                    exit; 
                }
            }
        }
    }

    public function __destruct() {
        // 關閉資料庫連接
        if ($this->db) {
            $this->db->close();
            exit;
        }
    }
}
?>


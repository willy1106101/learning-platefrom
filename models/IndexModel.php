<?php
    class IndexModel {
        private $db;

        public function __construct($host, $username, $password, $dbname) {
            // 初始化資料庫連接
            $this->db = new mysqli($host, $username, $password, $dbname);
            $this->db->set_charset("utf8mb4");
            if ($this->db->connect_error) {
                die("Connection failed: " . $this->db->connect_error);
            }
        }

        public function getData() {
            // 返回靜態的表單資料
            return [
                'form_username' => '帳號',
                'form_password' => '密碼',
                'form_stdId' => '學號',
                'form_stdname' => '姓名'
            ];
        }

        public function findUserByStudentUsername($username) {
            $query = "SELECT * FROM students WHERE stdId = ?";
            $stmt = $this->db->prepare($query);

            if (!$stmt) {
                die("Statement preparation failed: " . $this->db->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // 檢查是否有結果
            if ($result->num_rows > 0) {
                return $result->fetch_object(); // 返回物件
            } else {
                return null; // 如果沒有找到用戶則返回 null
            }
        }

        public function findUserByTeacherUsername($username) {
            $query = "SELECT * FROM teacher WHERE username = ?";
            $stmt = $this->db->prepare($query);

            if (!$stmt) {
                die("Statement preparation failed: " . $this->db->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // 檢查是否有結果
            if ($result->num_rows > 0) {
                return $result->fetch_object(); // 返回物件
            } else {
                return null; // 如果沒有找到用戶則返回 null
            }
        }

        public function __destruct() {
            // 關閉資料庫連接
            if ($this->db) {
                $this->db->close();
            }
        }
    }
?>

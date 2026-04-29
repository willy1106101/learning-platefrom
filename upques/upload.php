<?php
require '../config/db.php';
$conn = mysqli_connect($host, $username, $password, $dbname);
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$conn->set_charset("utf8mb4");
// 檢查是否提交了表單
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $que_type = $conn->real_escape_string($_POST['que_type']);
    $typename = isset($_POST['typename']) ? $conn->real_escape_string($_POST['typename']) : '';

    // 如果有新分類名稱，先將其添加到 question_type 表中
    if (!empty($typename)) {
        // 檢查分類名稱是否已存在
        $stmt = $conn->prepare("SELECT id FROM question_type WHERE `typename` = ?");
        $stmt->bind_param("s", $typename);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // 如果分類名稱已存在，獲取其 ID
            $row = $result->fetch_assoc();
            $que_type = $row['id'];
        } else {
            // 插入新分類名稱
            $stmt = $conn->prepare("INSERT INTO question_type (`typename`) VALUES (?)");
            $stmt->bind_param("s", $typename);
            if ($stmt->execute()) {
                $que_type = $stmt->insert_id; // 獲取新插入的 ID
            } else {
                echo "錯誤: 無法新增分類名稱。";
                exit;
            }
        }
        $stmt->close(); // 關閉查詢語句
    }
    // 大題內容
    $dcontent = $conn->real_escape_string($_POST['content']);
    $content = str_replace(["\\r\\n", "\\r", "\\n"], '', $dcontent); // 移除所有的\r\n

    // 圖片上傳邏輯
    $targetDir = "../assets/upload/";
    $imageUrl = NULL;

    // 檢查目錄是否存在，若不存在則創建
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true); // true 代表會自動遞迴創建父目錄
    }

    // 檢查並處理大題圖片上傳
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $targetFile = $targetDir . uniqid() . "." . $imageFileType;

        $allowedTypes = array("jpg", "jpeg", "png", "gif");
        // if (in_array($imageFileType, $allowedTypes)) {
        if(true){
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                chmod($targetFile, 0644);
                $imageUrl = $targetFile;
            } else {
                echo "圖片上傳失敗。";
                exit;
            }
        } else {
            echo "只允許 JPG, JPEG, PNG, GIF 格式的圖片。";
            exit;
        }
    }

    // 插入大題資料
    $sql = "INSERT INTO questionsets (content, image_url,que_type) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $imageUrl = str_replace(array("../../", "../", "../"), '', $imageUrl ?? '');
    $stmt->bind_param('ssi', $content, $imageUrl,$que_type);

    if ($stmt->execute()) {
        $questionSetId = $stmt->insert_id;

        // 處理每個小題及其選項
        foreach ($_POST['questions'] as $index => $questionData) {
            $questionText = str_replace(["\\r\\n", "\\r", "\\n"], '',$conn->real_escape_string($questionData['text']));
            $correctOption = strtoupper($conn->real_escape_string($questionData['correct_option']));

            // 插入小題資料
            $sql = "INSERT INTO questions (question_set_id, question_text, correct_option) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iss', $questionSetId,  $questionText, $correctOption);

            if ($stmt->execute()) {
                $questionId = $stmt->insert_id;

                // 處理選項
                foreach ($questionData['options'] as $optionLetter => $optionData) {
                    $optionText = str_replace(["\\r\\n", "\\r", "\\n"], '',$conn->real_escape_string($optionData['text']));
                    $optionImageUrl = NULL;

                    // 處理選項圖片上傳
                    if (isset($_FILES['questions']['name'][$index]['options'][$optionLetter]['image']) &&
                        $_FILES['questions']['error'][$index]['options'][$optionLetter]['image'] == 0) {
                        $imageFileType = strtolower(pathinfo($_FILES['questions']['name'][$index]['options'][$optionLetter]['image'], PATHINFO_EXTENSION));
                        $targetFile = $targetDir . uniqid() . "." . $imageFileType;

                        if (true) {
                            if (move_uploaded_file($_FILES['questions']['tmp_name'][$index]['options'][$optionLetter]['image'], $targetFile)) {
                                $optionImageUrl = str_replace(array("../../", "../", "../"), '',$targetFile??'');
                            } else {
                                echo "選項圖片上傳失敗。";
                            }
                        } else {
                            echo "選項圖片格式不正確。";
                        }
                    }

                    if(isset($optionText) && $optionText !== '' || isset($optionImageUrl) && $optionImageUrl !== ''){
                    // 插入選項資料
                    $sql = "INSERT INTO options (question_id, option_letter, option_text, option_image) 
                            VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('isss', $questionId, $optionLetter,  $optionText, $optionImageUrl);
                    $stmt->execute();
                    }
                }
            }
        }

        echo "題目上傳成功！";
        echo "<a href='insertque.php'>返回</a>";
    } else {
        echo "錯誤: " . $stmt->error;
    }

    // 關閉資料庫連接
    $stmt->close();
    $conn->close();
}
?>

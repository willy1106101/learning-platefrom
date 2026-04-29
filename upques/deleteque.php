<?php
require "../config/db.php";
$conn = mysqli_connect($host, $username, $password, $dbname);
if (mysqli_connect_errno())
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$conn->set_charset("utf8mb4");

// 檢查是否提交了刪除請求
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $questionSetId = intval($_POST['delete_id']);

    // 刪除大題圖片
    $sql = "SELECT image_url FROM questionsets WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $questionSetId);
    $stmt->execute();
    $stmt->bind_result($imageUrl);
    $stmt->fetch();
    $stmt->close();

    if ($imageUrl && file_exists("../../" . $imageUrl)) {
        unlink("../../" . $imageUrl); // 刪除大題圖片
    }

    // 刪除所有小題的選項圖片
    $sql = "SELECT options.option_image FROM options
            INNER JOIN questions ON options.question_id = questions.id
            WHERE questions.question_set_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $questionSetId);
    $stmt->execute();
    $stmt->bind_result($optionImageUrl);
    while ($stmt->fetch()) {
        if ($optionImageUrl && file_exists("../../" . $optionImageUrl)) {
            unlink("../../" . $optionImageUrl); // 刪除選項圖片
        }
    }
    $stmt->close();

    // 刪除選項、問題及大題的數據
    $conn->query("DELETE FROM options WHERE question_id IN (SELECT id FROM questions WHERE question_set_id = $questionSetId)");
    $conn->query("DELETE FROM questions WHERE question_set_id = $questionSetId");
    $stmt = $conn->prepare("DELETE FROM questionsets WHERE id = ?");
    $stmt->bind_param('i', $questionSetId);

    // 執行刪除並確認
    if ($stmt->execute()) {
        echo "題目及所有相關圖片已成功刪除！";
        echo "<a href='index.php'>返回</a>";
    } else {
        echo "錯誤: " . $stmt->error;
    }

    // 關閉資料庫連接
    $stmt->close();
    $conn->close();
}
?>

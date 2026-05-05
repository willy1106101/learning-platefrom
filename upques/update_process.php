<?php
require '../config/db.php';

$conn = mysqli_connect($host, $username, $password, $dbname);
if (mysqli_connect_errno()) {
    die("DB error: " . mysqli_connect_error());
}
$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $setId = (int)$_POST['question_set_id'];

    /* ===== 大題資料 ===== */
    $content = $_POST['content'];
    $que_type = (int)$_POST['que_type'];

    /* ===== 圖片 ===== */
    $targetDir = "../assets/upload/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

    $imageUrl = null;

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file = $targetDir . uniqid() . "." . $ext;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $file)) {
            $imageUrl = str_replace(["../","../../"], '', $file);
        }
    }

    /* ===== 更新大題 ===== */
    if ($imageUrl) {
        $stmt = $conn->prepare("UPDATE questionsets SET content=?, image_url=?, que_type=? WHERE id=?");
        $stmt->bind_param("ssii", $content, $imageUrl, $que_type, $setId);
    } else {
        $stmt = $conn->prepare("UPDATE questionsets SET content=?, que_type=? WHERE id=?");
        $stmt->bind_param("sii", $content, $que_type, $setId);
    }
    $stmt->execute();

    /* =======================================================
       ⚠️ 核心策略：刪除重建（穩定版）
    ======================================================= */

    // 刪 options
    $conn->query("DELETE o FROM options o
                  JOIN questions q ON o.question_id = q.id
                  WHERE q.question_set_id = $setId");

    // 刪 questions
    $conn->query("DELETE FROM questions WHERE question_set_id = $setId");

    /* ===== 重建小題 ===== */
    foreach ($_POST['questions'] as $qKey => $q) {

        $text = $conn->real_escape_string($q['text']);
        $correct = strtoupper($q['correct_option']);

        $stmt = $conn->prepare("INSERT INTO questions (question_set_id, question_text, correct_option)
                                VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $setId, $text, $correct);
        $stmt->execute();

        $qId = $stmt->insert_id;

        /* ===== options ===== */
        foreach ($q['options'] as $letter => $opt) {

            $optText = $conn->real_escape_string($opt['text'] ?? '');
            $optImg = null;

            if (!empty($_FILES['questions']['name'][$qKey]['options'][$letter]['image'])) {
                $ext = pathinfo($_FILES['questions']['name'][$qKey]['options'][$letter]['image'], PATHINFO_EXTENSION);
                $file = $targetDir . uniqid() . "." . $ext;

                if (move_uploaded_file($_FILES['questions']['tmp_name'][$qKey]['options'][$letter]['image'], $file)) {
                    $optImg = str_replace(["../","../../"], '', $file);
                }
            }

            if ($optText !== '' || $optImg !== null) {
                $stmt = $conn->prepare("INSERT INTO options (question_id, option_letter, option_text, option_image)
                                        VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $qId, $letter, $optText, $optImg);
                $stmt->execute();
            }
        }
    }

    echo "更新成功！<a href='index.php'>返回</a>";
}
?>
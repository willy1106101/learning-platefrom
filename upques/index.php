<?php
require "../config/db.php";
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 30px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .btn-upload {
            margin-bottom: 20px;
        }
        table img {
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .question-table td {
            vertical-align: middle;
        }
        .sub-question {
            background-color: #fdfdfd;
            padding: 10px 15px;
            border-left: 3px solid #0d6efd;
            margin-bottom: 5px;
        }
        .option-text {
            display: inline-block;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="page-title">題目列表</h2>
        
        <a href="insertque.php" class="btn btn-primary btn-upload">上傳題目</a>
        <a href="../teacher" class="btn btn-primary btn-upload">回主頁</a>

        <div class="card shadow">
            <div class="card-body">
                <table class="table table-bordered table-hover table-striped question-table">
                    <thead class="table-primary">
                        <tr>
                            <th width="5%">序號<br>答案</th>
                            <th>題目內容</th>
                            <th width="10%">功能</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $conn = mysqli_connect($host, $username, $password, $dbname);
                            if (mysqli_connect_errno()) {
                                echo "<tr><td colspan='3'>資料庫連線失敗：" . mysqli_connect_error() . "</td></tr>";
                            }
                            $conn->set_charset("utf8mb4");

                            $sql = "SELECT id, content, image_url FROM questionsets ORDER BY id ASC";
                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                $index = 1;
                                while ($questionSet = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td>' . $index++ . '</td>';
                                    echo '<td>';
                                    if (!empty($questionSet["content"])) {
                                        echo '<strong>' . htmlspecialchars($questionSet["content"]) . '</strong>';
                                    }
                                    if (!empty($questionSet["image_url"])) {
                                        echo '<br><img src="../' . htmlspecialchars($questionSet["image_url"]) . '" width="80" alt="大題圖片">';
                                    }
                                    echo '</td>';
                                    echo "<td>
                                            <form action='deleteque.php' method='POST' onsubmit='return confirm(\"確定要刪除這個題目嗎？\");'>
                                                <input type='hidden' name='delete_id' value='" . $questionSet['id'] . "'>
                                                <button type='submit' class='btn btn-danger btn-sm'>刪除</button>
                                            </form>
                                        </td>";
                                    echo '</tr>';

                                    // 小題部分
                                    $questionSql = "SELECT id, question_text, correct_option FROM questions WHERE question_set_id = " . $questionSet['id'] . " ORDER BY id";
                                    $questionResult = $conn->query($questionSql);

                                    if ($questionResult->num_rows > 0) {
                                        while ($question = $questionResult->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($question["correct_option"]) . '</td>';
                                            echo '<td colspan="2">';
                                            echo '<div class="sub-question">';
                                            echo '<strong>' . htmlspecialchars($question["question_text"]) . '</strong>';

                                            // 顯示選項
                                            $optionSql = "SELECT option_letter, option_text, option_image FROM options WHERE question_id = " . $question['id'] . " ORDER BY id ASC";
                                            $optionResult = $conn->query($optionSql);

                                            if ($optionResult->num_rows > 0) {
                                                echo '<br>';
                                                while ($option = $optionResult->fetch_assoc()) {
                                                    echo '<span class="option-text"><strong>' . htmlspecialchars($option["option_letter"]) . '.</strong> ' . htmlspecialchars($option["option_text"]);
                                                    if (!empty($option["option_image"])) {
                                                        echo ' <img src="../' . htmlspecialchars($option["option_image"]) . '" width="50" alt="選項圖片">';
                                                    }
                                                    echo '</span>';
                                                }
                                            }
                                            echo '</div>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                }
                            } else {
                                echo "<tr><td colspan='3'>沒有找到大題資料。</td></tr>";
                            }
                            $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

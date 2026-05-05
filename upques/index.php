<?php
require "../config/db.php";

$conn = mysqli_connect($host, $username, $password, $dbname);
if (mysqli_connect_errno()) {
    die("資料庫連線失敗：" . mysqli_connect_error());
}
$conn->set_charset("utf8mb4");

/* ===== 分頁設定 ===== */
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 5; // 每頁顯示幾筆（可自行調整）
$offset = ($page - 1) * $perPage;

/* ===== 總筆數 ===== */
$countSql = "SELECT COUNT(*) as total FROM questionsets";
$countResult = $conn->query($countSql);
$totalRow = $countResult->fetch_assoc();
$totalPages = ceil($totalRow['total'] / $perPage);

/* ===== 分頁查詢 ===== */
$sql = "SELECT id, content, image_url 
        FROM questionsets 
        ORDER BY id ASC 
        LIMIT $perPage OFFSET $offset";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<title>題目列表</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.container { margin-top: 30px; }
.page-title { text-align: center; margin-bottom: 20px; font-weight: bold; }
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

    <a href="insertque.php" class="btn btn-primary mb-2">上傳題目</a>
    <a href="../teacher" class="btn btn-secondary mb-2">回主頁</a>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped">
                <thead class="table-primary">
                    <tr>
                        <th width="5%">序號<br>答案</th>
                        <th>題目內容</th>
                        <th width="10%">功能</th>
                    </tr>
                </thead>
                <tbody>

<?php
if ($result->num_rows > 0) {
    $index = ($page - 1) * $perPage + 1;

    while ($questionSet = $result->fetch_assoc()) {

        echo '<tr>';
        echo '<td>' . $index++ . '</td>';

        echo '<td>';
        echo '<strong>' . htmlspecialchars($questionSet["content"]) . '</strong>';

        if (!empty($questionSet["image_url"])) {
            echo '<br><img src="../' . htmlspecialchars($questionSet["image_url"]) . '" width="80">';
        }
        echo '</td>';

        echo "<td>
                <form action='updateque.php' method='GET'>
                    <input type='hidden' name='id' value='{$questionSet['id']}'>
                    <button class='btn btn-success btn-sm'>編輯</button>
                </form>
                <form action='deleteque.php' method='POST' onsubmit='return confirm(\"確定刪除?\");'>
                    <input type='hidden' name='delete_id' value='{$questionSet['id']}'>
                    <button class='btn btn-danger btn-sm'>刪除</button>
                </form>
              </td>";
        echo '</tr>';

        /* ===== 小題 ===== */
        $questionSql = "SELECT id, question_text, correct_option 
                        FROM questions 
                        WHERE question_set_id = {$questionSet['id']}";

        $questionResult = $conn->query($questionSql);

        while ($question = $questionResult->fetch_assoc()) {

            echo '<tr>';
            echo '<td>' . htmlspecialchars($question["correct_option"]) . '</td>';
            echo '<td colspan="2">';
            echo '<div class="sub-question">';
            echo '<strong>' . htmlspecialchars($question["question_text"]) . '</strong>';

            /* ===== 選項 ===== */
            $optionSql = "SELECT option_letter, option_text, option_image 
                          FROM options 
                          WHERE question_id = {$question['id']}";

            $optionResult = $conn->query($optionSql);

            echo '<br>';
            while ($option = $optionResult->fetch_assoc()) {
                echo '<span class="option-text">';
                echo '<strong>' . htmlspecialchars($option["option_letter"]) . '.</strong> ';
                echo htmlspecialchars($option["option_text"]);

                if (!empty($option["option_image"])) {
                    echo ' <img src="../' . htmlspecialchars($option["option_image"]) . '" width="50">';
                }
                echo '</span>';
            }

            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
    }

} else {
    echo "<tr><td colspan='3'>沒有資料</td></tr>";
}
?>

                </tbody>
            </table>

            <!-- ===== 分頁 UI（最多5頁） ===== -->
            <nav>
                <ul class="pagination justify-content-center">

                    <!-- 上一頁 -->
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">上一頁</a>
                    </li>

                    <?php
                    $range = 2; // 前後各2頁 → 共5頁

                    $start = max(1, $page - $range);
                    $end = min($totalPages, $page + $range);

                    // 確保最多顯示5頁
                    if ($end - $start < 4) {
                        if ($start == 1) {
                            $end = min($totalPages, $start + 4);
                        } elseif ($end == $totalPages) {
                            $start = max(1, $end - 4);
                        }
                    }
                    ?>

                    <!-- 開頭省略 -->
                    <?php if ($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1">1</a>
                        </li>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php endif; ?>

                    <!-- 中間頁碼 -->
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- 結尾省略 -->
                    <?php if ($end < $totalPages): ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $totalPages ?>">
                                <?= $totalPages ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- 下一頁 -->
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">下一頁</a>
                    </li>

                </ul>
            </nav>

        </div>
    </div>
</div>

</body>
</html>
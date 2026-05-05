<?php
require "../config/db.php";

// 1. 建立連線並檢查
$conn = mysqli_connect($host, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. 獲取大題資料
$sql_set = "SELECT * FROM `questionsets` WHERE id = $id";
$res_set = $conn->query($sql_set);
$setData = $res_set->fetch_assoc();

if (!$setData) {
    die("找不到該題目資料");
}

// 3. 獲取小題及其選項資料 (整理成陣列方便 JS 使用)
$sql_que = "SELECT q.id as qid, q.question_text, q.correct_option, o.option_letter, o.option_text, o.option_image 
            FROM `questions` q 
            LEFT JOIN `options` o ON q.id = o.question_id 
            WHERE q.question_set_id = $id 
            ORDER BY q.id ASC, o.option_letter ASC";
$res_que = $conn->query($sql_que);

$questions = [];
while ($row = $res_que->fetch_assoc()) {
    $qid = $row['qid'];
    if (!isset($questions[$qid])) {
        $questions[$qid] = [
            'text' => $row['question_text'],
            'correct' => $row['correct_option'],
            'options' => []
        ];
    }
    if ($row['option_letter']) {
        $questions[$qid]['options'][$row['option_letter']] = [
            'text' => $row['option_text'],
            'image' => $row['option_image']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>編輯題目</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* 保留你原本的 CSS */
        body { background-color: #f8f9fa; }
        .container { max-width: 900px; }
        .page-title { text-align: center; margin-bottom: 30px; font-weight: bold; color: #fd7e14; }
        .card { padding: 20px; border-radius: 10px; }
        .question-block { border: 1px solid #dee2e6; background-color: #ffffff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .option-block { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .btn-upload { display: block; margin: 30px auto 0; width: 200px; font-size: 1.1rem; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="page-title">編輯題目 (ID: <?php echo $id; ?>)</h1>
    <a href="index.php" class="btn btn-secondary mb-3">取消並返回</a>

    <div class="card shadow">
        <form action="update_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="set_id" value="<?php echo $id; ?>">
            <input type="hidden" name="question_set_id" value="<?php echo $id; ?>">
            <div class="mb-3 row">
                <label for="que_type" class="col-sm-2 col-form-label fw-bold">分類</label>
                <div class="col-sm-10">
                    <select class="form-select" id="que_type" name="que_type">
                        <?php
                        $sql_type = "SELECT * FROM `question_type`"; 
                        $res_type = $conn->query($sql_type);
                        while ($row = $res_type->fetch_assoc()) {
                            $selected = ($row['id'] == $setData['que_type']) ? "selected" : "";
                            echo "<option value='" . $row['id'] . "' $selected>" . $row['typename'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="content" class="form-label fw-bold">大題內容:</label>
                <textarea id="content" name="content" class="form-control" rows="3"><?php echo htmlspecialchars($setData['content']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">目前圖片:</label><br>
                <?php if($setData['image_url']): ?>
                    <img src="../<?php echo $setData['image_url']; ?>" style="max-width: 200px;" class="mb-2 border">
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-control">
                <small class="text-muted">若不更換圖片請留空</small>
            </div>

            <hr>

            <div id="questions-container">
                </div>

            <button type="button" class="btn btn-success w-100 mt-3" onclick="addQuestion()">添加新小題</button>
            <input type="submit" class="btn btn-warning btn-upload" value="儲存修改">
        </form>
    </div>
</div>

<script>
// 將 PHP 的小題資料轉換為 JS 陣列
const initialQuestions = <?php echo json_encode(array_values($questions)); ?>;

// 頁面載入時初始化
document.addEventListener('DOMContentLoaded', function() {
    if (initialQuestions.length > 0) {
        initialQuestions.forEach((q, index) => {
            renderQuestion(index + 1, q);
        });
    } else {
        addQuestion();
    }
});

function renderQuestion(number, data = null) {
    const container = document.getElementById('questions-container');
    const questionText = data ? data.text : "";
    const correctOption = data ? data.correct : "";
    const options = data ? data.options : { 'A': {text:''}, 'B': {text:''}, 'C': {text:''}, 'D': {text:''} };

    let optionsHtml = '';
    Object.keys(options).forEach(letter => {
        optionsHtml += `
            <div class="option-block">
                <label>${letter}:</label>
                <input type="text" name="questions[${number}][options][${letter}][text]" value="${options[letter].text || ''}" class="form-control w-50">
                <input type="file" name="questions[${number}][options][${letter}][image]" class="form-control w-50">
                <img src="../${options[letter].image}" style="max-width: 100px;" class="mb-2 border">
            </div>
        `;
    });

    const html = `
        <div class="question-block" data-question-number="${number}">
            <h3>小題 ${number}</h3>
            <div class="mb-3">
                <label class="form-label">小題內容:</label>
                <textarea name="questions[${number}][text]" class="form-control" rows="2">${questionText}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">正確答案:</label>
                <select name="questions[${number}][correct_option]" 
                    class="form-select correct-option" required>
                    <option value="">請選擇答案!!</option>
                    ${Object.keys(options).map(l => 
                        `<option value="${l}" ${l == correctOption ? 'selected' : ''}>${l}</option>`
                    ).join('')}
                </select>
            </div>
            <h5 class="mb-2">選項</h5>
            <div class="options-container">${optionsHtml}</div>
            <div class="action-buttons mt-3">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addOption(this)">新增選項</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">刪除小題</button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function addQuestion() {
    const container = document.getElementById('questions-container');
    const newNumber = container.querySelectorAll('.question-block').length + 1;
    renderQuestion(newNumber);
}

function removeQuestion(button) {
    if(confirm('確定要刪除此小題嗎？')) {
        button.closest('.question-block').remove();
        renumberQuestions();
    }
}

function addOption(button) {
    const questionBlock = button.closest('.question-block');
    const optionsContainer = questionBlock.querySelector('.options-container');
    const correctOptionSelect = questionBlock.querySelector('.correct-option');
    const existingOptions = optionsContainer.querySelectorAll('.option-block').length;
    const newOptionLabel = String.fromCharCode(65 + existingOptions);
    if (newOptionLabel > 'Z') return;

    const questionNumber = questionBlock.getAttribute('data-question-number');
    const newOption = `
        <div class="option-block">
            <label>${newOptionLabel}:</label>
            <input type="text" name="questions[${questionNumber}][options][${newOptionLabel}][text]" class="form-control w-50">
            <input type="file" name="questions[${questionNumber}][options][${newOptionLabel}][image]" class="form-control w-50">
        </div>
    `;
    optionsContainer.insertAdjacentHTML('beforeend', newOption);

    const newOptEl = document.createElement('option');
    newOptEl.value = newOptionLabel;
    newOptEl.textContent = newOptionLabel;
    correctOptionSelect.appendChild(newOptEl);
}

function renumberQuestions() {
    const questionBlocks = document.querySelectorAll('.question-block');
    questionBlocks.forEach((block, index) => {
        const number = index + 1;
        block.setAttribute('data-question-number', number);
        block.querySelector('h3').innerText = `小題 ${number}`;
        block.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/questions\[\d+\]/, `questions[${number}]`);
        });
    });
}
</script>
</body>
</html>
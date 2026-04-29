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
            max-width: 900px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #0d6efd;
        }
        .card {
            padding: 20px;
            border-radius: 10px;
        }
        .question-block {
            border: 1px solid #dee2e6;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            position: relative;
        }
        .question-block h3 {
            color: #495057;
            margin-bottom: 15px;
        }
        .option-block {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        .option-block input[type="text"] {
            flex: 1;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn-success.main-add {
            width: 100%;
            font-size: 1.2rem;
        }
        .btn-upload {
            display: block;
            margin: 30px auto 0;
            width: 200px;
            font-size: 1.1rem;
        }
        .btn-upload-1 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="page-title">上傳題目</h1>
    <a href="../teacher" class="btn btn-primary btn-upload-1">回主頁</a>
    <a href="index.php" class="btn btn-primary btn-upload-1">題目列表</a>
    <div class="card shadow">
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3 row">
                <label for="que_type" class="col-sm-2 col-form-label fw-bold">分類</label>
                <div class="col-sm-10">
                    <select class="form-select" id="que_type" name="que_type">
                        <option value="7">請選擇分類</option>
                        <?php
                        $conn = mysqli_connect($host, $username, $password, $dbname);
                        if (mysqli_connect_errno()) {
                            echo "Failed to connect to MySQL: " . mysqli_connect_error();
                        }
                        $conn->set_charset("utf8mb4");
                        $sql = "SELECT * FROM `question_type`"; 
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['id'] . "'>" . $row['typename'] . "</option>";
                            }
                        } else {
                            echo "<option value=''>沒有分類資料</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="typename" class="form-label fw-bold">或輸入新分類名稱:</label>
                <input type="text" id="typename" name="typename" class="form-control">
            </div>
            <div class="mb-3">
                <label for="content" class="form-label fw-bold">大題內容:</label>
                <textarea id="content" name="content" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label fw-bold">上傳大題圖片:</label>
                <input type="file" id="image" name="image" class="form-control">
            </div>

            <!-- 小題區域 -->
            <div id="questions-container">
                <div class="question-block" data-question-number="1">
                    <h3>小題 1</h3>
                    <div class="mb-3">
                        <label class="form-label">小題內容:</label>
                        <textarea name="questions[1][text]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">正確答案:</label>
                        <select name="questions[1][correct_option]" class="form-select correct-option" required>
                            <option value="">請選擇答案!!</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                    <h5 class="mb-2">選項</h5>
                    <div class="options-container">
                        <div class="option-block">
                            <label>A:</label>
                            <input type="text" name="questions[1][options][A][text]" class="form-control w-50">
                            <input type="file" name="questions[1][options][A][image]" class="form-control w-50">
                        </div>
                        <div class="option-block">
                            <label>B:</label>
                            <input type="text" name="questions[1][options][B][text]" class="form-control w-50">
                            <input type="file" name="questions[1][options][B][image]" class="form-control w-50">
                        </div>
                        <div class="option-block">
                            <label>C:</label>
                            <input type="text" name="questions[1][options][C][text]" class="form-control w-50">
                            <input type="file" name="questions[1][options][C][image]" class="form-control w-50">
                        </div>
                        <div class="option-block">
                            <label>D:</label>
                            <input type="text" name="questions[1][options][D][text]" class="form-control w-50">
                            <input type="file" name="questions[1][options][D][image]" class="form-control w-50">
                        </div>
                    </div>
                    <div class="action-buttons mt-3">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addOption(this)">新增選項</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="copyQuestion(this)">複製小題</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">刪除小題</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-success main-add mt-3" onclick="addQuestion()">添加小題</button>
            <input type="submit" class="btn btn-primary btn-upload" value="上傳">
        </form>
    </div>
</div>

<script>
function addQuestion() {
    const container = document.getElementById('questions-container');
    const newNumber = container.querySelectorAll('.question-block').length + 1;
    const newQuestion = createQuestionBlock(newNumber);
    container.insertAdjacentHTML('beforeend', newQuestion);
}

function copyQuestion(button) {
    const container = document.getElementById('questions-container');
    const questionBlock = button.closest('.question-block');
    const clonedBlock = questionBlock.cloneNode(true);
    container.appendChild(clonedBlock);
    renumberQuestions();
}

function removeQuestion(button) {
    button.closest('.question-block').remove();
    renumberQuestions();
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
    const newOptionElement = document.createElement('option');
    newOptionElement.value = newOptionLabel;
    newOptionElement.textContent = newOptionLabel;
    correctOptionSelect.appendChild(newOptionElement);
}

function createQuestionBlock(number) {
    return `
        <div class="question-block" data-question-number="${number}">
            <h3>小題 ${number}</h3>
            <div class="mb-3">
                <label class="form-label">小題內容:</label>
                <textarea name="questions[${number}][text]" class="form-control" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">正確答案:</label>
                <select name="questions[${number}][correct_option]" class="form-select correct-option" required>
                    <option value="">請選擇答案!!</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                </select>
            </div>
            <h5 class="mb-2">選項</h5>
            <div class="options-container">
                <div class="option-block">
                    <label>A:</label>
                    <input type="text" name="questions[${number}][options][A][text]" class="form-control w-50">
                    <input type="file" name="questions[${number}][options][A][image]" class="form-control w-50">
                </div>
                <div class="option-block">
                    <label>B:</label>
                    <input type="text" name="questions[${number}][options][B][text]" class="form-control w-50">
                    <input type="file" name="questions[${number}][options][B][image]" class="form-control w-50">
                </div>
            </div>
            <div class="action-buttons mt-3">
                <button type="button" class="btn btn-secondary btn-sm" onclick="addOption(this)">新增選項</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="copyQuestion(this)">複製小題</button>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeQuestion(this)">刪除小題</button>
            </div>
        </div>
    `;
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

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>

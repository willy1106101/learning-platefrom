<?php
    class StudentView {
        public function render($data,$showExamList,$ShowExamQuetypeList) {
?>
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    body {
        background-color: #f8f9fa;
        color: #333;
    }

    .custom-container {
        max-width: 900px;
        margin-top: 2rem;
        margin-bottom: 3rem;
    }

    /* 頂部導覽美化 */
    .navbar-custom {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 0.8rem 1.5rem;
    }

    /* 開始練習卡片 */
    .card-practice {
        border: none;
        border-radius: 20px;
        background: white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.03);
        transition: transform 0.3s ease;
    }

    .form-label-custom {
        font-weight: 600;
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* 按鈕美化 */
    .btn-gradient-success {
        background: var(--success-gradient);
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-gradient-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        color: white;
    }

    /* 表格美化 */
    .table-container {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .table thead {
        background-color: #fdfdfd;
    }
    .table thead th {
        border-bottom: 2px solid #f1f1f1;
        color: #adb5bd;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .score-text {
        font-weight: 700;
        color: #28a745;
    }
    .view-link {
        color: #0d6efd;
        font-weight: 500;
        transition: 0.2s;
    }
    .view-link:hover {
        color: #0a58ca;
        text-decoration: underline !important;
    }
</style>

<div class="container custom-container">
    <nav class="navbar navbar-custom mb-4">
        <div class="container-fluid">
            <span class="fw-bold text-dark">
                <i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($data['name']); ?>
            </span>
            <a href="./logout" class="btn btn-sm btn-outline-danger px-3 rounded-pill">
                <i class="bi bi-box-arrow-right me-1"></i>登出
            </a>
        </div>
    </nav>

    <div class="card card-practice p-4 mb-4">
        <h5 class="mb-4 fw-bold text-dark"><i class="bi bi-rocket-takeoff me-2 text-success"></i>開始新練習</h5>
        <form action="./requiz" class="row g-3 align-items-end" method="post">
            <div class="col-md-5">
                <label class="form-label-custom">選擇練習章節</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-bookmarks"></i></span>
                    <select class="form-select border-0 bg-light" name="quetype">
                        <option disabled selected>請選擇章節...</option>
                        <?php
                            if (!empty($ShowExamQuetypeList)) {
                                echo '<option value="">全部章節</option>';
                                foreach ($ShowExamQuetypeList as $quetypeRow) {
                                    echo "<option value='" . $quetypeRow['id'] . "'>" . htmlspecialchars($quetypeRow['typename']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>沒有章節資料</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">設定練習題數</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-list-ol"></i></span>
                    <input type="number" class="form-control border-0 bg-light" name="questionnum" min="1" value="50">
                </div>
            </div>
            <div class="col-md-3">
                <button class="btn btn-gradient-success w-100">開始練習</button>
            </div>
        </form>
    </div>

    <div class="table-container p-2">
        <div class="px-3 pt-3 pb-2">
            <h6 class="fw-bold text-secondary"><i class="bi bi-clock-history me-2"></i>練習歷史紀錄(練習次數: <?php echo count($showExamList);?>次)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">考試時間</th>
                        <th class="text-center">題目回顧</th>
                        <th class="text-end pe-4">總成績</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if (!empty($showExamList)) {
                            foreach ($showExamList as $row) {
                                echo '<tr>
                                        <td class="ps-4 text-muted">' . htmlspecialchars($row['quiztime']) . '</td>
                                        <td class="text-center">
                                            <a class="text-decoration-none view-link" href="./quiz?examid=' . $row['exam_id'] . '">
                                                <i class="bi bi-eye me-1"></i>查看
                                            </a>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="score-text">' . htmlspecialchars($row['score']) . ' 分</span>
                                        </td>
                                    </tr>';
                            }
                        } else {
                            echo '<tr>
                                    <td colspan="3" class="text-center py-5 text-muted">目前沒有練習紀錄，開始練習吧！</td>
                                </tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
        }
    }
?>
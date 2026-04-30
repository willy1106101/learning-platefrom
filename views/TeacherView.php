<?php
    class TeacherView {
        public function render($data,$showExamList,$editexamdata,$showexamclassData) {
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    body {
        background-color: #f4f7f6;
        font-family: 'Segoe UI', 'Microsoft JhengHei', sans-serif;
    }
    .main-wrapper {
        padding-top: 2rem;
        padding-bottom: 4rem;
    }
    /* 導覽列美化 */
    .navbar-custom {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 0.8rem 1.2rem;
    }
    .nav-link {
        font-weight: 500;
        color: #555;
        margin-right: 15px;
        transition: 0.3s;
    }
    .nav-link:hover { color: #28a745; }
    .nav-link.active {
        color: #28a745;
        border-bottom: 2px solid #28a745;
    }
    /* 主內容區卡片 */
    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        padding: 2.5rem;
        margin-top: 1.5rem;
    }
    /* 表格樣式優化 */
    .table thead th {
        background-color: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border-top: none;
    }
    .table tbody tr:hover { background-color: #fafafa; }
    .table td { vertical-align: middle; }
    /* 題目預覽樣式 */
    .que_op {
        background: #fdfdfd;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border-left: 5px solid #28a745;
    }
    .que_op .text { font-weight: 600; color: #333; margin-bottom: 15px; }
    .quelist_i { background: #fff; border-radius: 8px; border: 1px solid #f1f1f1; padding: 15px; margin-top: 10px; }
    .ques { color: #198754; font-weight: 500; }
    .option label { display: block; padding: 5px 0; cursor: pointer; }
    img { border-radius: 8px; margin: 10px 0; max-width: 100%; height: auto; }
</style>
<div class="container main-wrapper">
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom mb-4">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= !isset($_GET['p']) ? 'active' : '' ?>" href="./teacher">成績管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($_GET['p'] ?? '') === 'student' ? 'active' : '' ?>" href="./teacher?p=student">學生管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($_GET['p'] ?? '') === 'class' ? 'active' : '' ?>" href="./teacher?p=class">班級管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($_GET['p'] ?? '') === 'edit_own' ? 'active' : '' ?>" href="./teacher?p=edit_own">個資管理</a>
                    </li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 fw-bold text-secondary"><i class="bi bi-person-workspace me-1"></i><?php echo htmlspecialchars($data['username']); ?></span>
                <a href="./logout" class="btn btn-sm btn-outline-danger shadow-sm">登出</a>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <?php 
            // 學生管理列表
            if(isset($_GET['p']) && $_GET['p'] === 'student'){
        ?>
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold m-0 text-dark"><i class="bi bi-people-fill me-2 text-success"></i>學生管理</h4>
            <a href="./teacher?p=add_student" class="btn btn-success px-4 py-2 shadow-sm rounded-pill">
                <i class="bi bi-person-plus-fill me-2"></i>加入學生
            </a>
        </div>

        <div class="table-container shadow-sm bg-white p-3 rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">學號</th>
                            <th class="border-0">班級</th>
                            <th class="border-0">姓名</th>
                            <th class="border-0 text-center">管理功能</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($showExamList)) {
                                foreach ($showExamList as $row) {
                                    echo '<tr>
                                        <td class="ps-4 fw-bold text-primary">'.htmlspecialchars($row['stdId']).'</td>
                                        <td><span class="class-badge bg-light border text-dark px-3 py-1 rounded-pill small">'.htmlspecialchars($row['classname']??"未分配").'</span></td>
                                        <td class="fw-bold text-dark">'.htmlspecialchars($row['name']).'</td>
                                        <td class="text-center">
                                            <button onclick="window.location.href=\'./teacher?p=edit_student&studentid='.htmlspecialchars($row['id']).'\'" 
                                                    class="btn btn-outline-primary btn-sm px-3 rounded-pill me-1">
                                                <i class="bi bi-pencil-square me-1"></i>編輯
                                            </button>
                                            <button onclick="if (confirm(\'確定要刪除該學生嗎?\')) { window.location.href=\'./del_student&studentid='.htmlspecialchars($row['id']).'\'; }" 
                                                    class="btn btn-outline-danger btn-sm px-3 rounded-pill">
                                                <i class="bi bi-trash3 me-1"></i>刪除
                                            </button>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>目前沒有學生資料！</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
            // 學生管理-新增
            }else if(isset($_GET['p']) && $_GET['p'] === 'add_student'){
        ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-success text-white p-3 ps-4">
                <h5 class="m-0 fw-bold"><i class="bi bi-person-plus me-2"></i>新增學生資料</h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-light border border-dashed mb-4">
                    <div class="row align-items-center">
                        <label class="col-sm-3 fw-bold text-secondary mb-0"><i class="bi bi-file-earmark-excel me-2"></i>EXCEL 批量上傳(<a href="./assets/student.xlsx" class="icon-link">檔案</a>)</label>
                        <div class="col-sm-9">
                            <input type="file" class="form-control form-control-sm" onchange="addxlsxdata(this)" accept=".xlsx, .xls">
                        </div>
                    </div>
                </div>
                
                <form method="post" action="./save_add_student" class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">學號</label>
                        <input type="number" class="form-control px-3 py-2 rounded-3 bg-light border-0" id="stdId" name="stdId" placeholder="例如：11012345">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">班級</label>
                        <select class="form-select px-3 py-2 rounded-3 bg-light border-0" id="classid" name="classid" required>
                            <option value="">請選擇班級...</option>
                            <?php
                                if (!empty($showexamclassData)) {
                                    foreach ($showexamclassData as $classRow) {
                                        echo "<option value='" . $classRow['classid'] . "'>" . htmlspecialchars($classRow['classname']) . "</option>";
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold">姓名</label>
                        <input type="text" class="form-control px-3 py-2 rounded-3 bg-light border-0" id="name" name="name" placeholder="請輸入學生姓名">
                    </div>
                    <div class="col-12 mt-5 border-top pt-4">
                        <button type="submit" class="btn btn-success px-5 py-2 rounded-pill fw-bold">確認加入學生</button>
                        <a href="./teacher?p=student" class="btn btn-link text-secondary px-4 text-decoration-none">取消返回</a>
                    </div>
                </form>
            </div>
        </div>

        <?php
            // 學生管理-修改
            }else if(isset($_GET['p']) && $_GET['p'] === 'edit_student'){
        ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white p-3 ps-4">
                <h5 class="m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>修改學生資料</h5>
            </div>
            <div class="card-body p-4 text-start">
                <form method="post" action="./save_edit_student">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($editexamdata['id']) ?>">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">學號</label>
                            <input type="number" class="form-control px-3 py-2 rounded-3 bg-light border-0" id="stdId" name="stdId" value="<?= htmlspecialchars($editexamdata['stdId']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">班級</label>
                            <select class="form-select px-3 py-2 rounded-3 bg-light border-0" id="classid" name="classid" required>
                                <?php
                                    if (!empty($showexamclassData)) {
                                        foreach ($showexamclassData as $classRow) {
                                            $selected = ($classRow['classid'] == $editexamdata['class_id']) ? 'selected' : '';
                                            echo "<option value='" . $classRow['classid'] . "' ".$selected.">" . htmlspecialchars($classRow['classname']) . "</option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">姓名</label>
                            <input type="text" class="form-control px-3 py-2 rounded-3 bg-light border-0" id="name" name="name" value="<?= htmlspecialchars($editexamdata['name']) ?>">
                        </div>
                        <div class="col-12 mt-5 border-top pt-4 text-start">
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold">保存修改內容</button>
                            <a href="./teacher?p=student" class="btn btn-link text-secondary px-4 text-decoration-none">返回列表</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php
            // 班級管理
            }else if(isset($_GET['p']) && $_GET['p'] === 'class'){
        ?>
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold m-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>班級管理</h4>
            <a href="./teacher?p=add_class" class="btn btn-primary px-4 py-2 shadow-sm rounded-pill">
                <i class="bi bi-plus-circle me-2"></i>創建班級
            </a>
        </div>

        <div class="table-container shadow-sm bg-white p-3 rounded-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="border-0 ps-4">班級代號</th>
                            <th class="border-0">班級名稱</th>
                            <th class="border-0 text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($showExamList)) {
                                foreach ($showExamList as $row) {
                                    echo '<tr>
                                        <td class="ps-4 fw-bold">'.htmlspecialchars($row['classid']).'</td>
                                        <td class="fw-bold text-dark">' . htmlspecialchars($row['classname']) . '</td>
                                        <td class="text-center">
                                            <button onclick="window.location.href=\'./teacher?p=edit_class&classid='.htmlspecialchars($row['classid']).'\'" class="btn btn-sm btn-outline-primary px-3 rounded-pill me-1">編輯</button>
                                            <button onclick="if (confirm(\'確定要刪除嗎?\')) { window.location.href=\'./del_class&classid='.htmlspecialchars($row['classid']).'\'; }" class="btn btn-sm btn-outline-danger px-3 rounded-pill">刪除</button>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="3" class="text-center py-5 text-muted">目前尚未建立班級資料</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php
            // 班級管理-新增
            }else if(isset($_GET['p']) && $_GET['p'] === 'add_class'){
        ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-3 ps-4">
                <h5 class="m-0 fw-bold"><i class="bi bi-plus-square me-2"></i>創建新班級</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" action="./save_add_class">
                    <div class="mb-4">
                        <label class="form-label fw-bold">班級名稱</label>
                        <input type="text" class="form-control px-3 py-2 rounded-3 bg-light border-0 text-start" name="classname" placeholder="請輸入班級名稱 (如：資管一甲)" required>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold">立即創建</button>
                        <a href="./teacher?p=class" class="btn btn-link text-secondary px-4 text-decoration-none">取消</a>
                    </div>
                </form>
            </div>
        </div>

        <?php
            // 班級管理-修改
            }else if(isset($_GET['p']) && $_GET['p'] === 'edit_class'){
        ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white p-3 ps-4">
                <h5 class="m-0 fw-bold"><i class="bi bi-pencil-square me-2"></i>修改班級名稱</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" action="./save_edit_class">
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold text-muted small">班級代號 (不可修改)</label>
                        <input type="text" class="form-control px-3 py-2 rounded-3 bg-light border-0 mb-3" name="classid" value="<?= htmlspecialchars($editexamdata['classid']) ?>" readonly>
                        
                        <label class="form-label fw-bold">班級名稱</label>
                        <input type="text" class="form-control px-3 py-2 rounded-3 bg-white border border-primary text-start" name="classname" value="<?= htmlspecialchars($editexamdata['classname']) ?>" required>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success px-5 py-2 rounded-pill fw-bold">更新班級名稱</button>
                        <a href="./teacher?p=class" class="btn btn-link text-secondary px-4 text-decoration-none">取消返回</a>
                    </div>
                </form>
            </div>
        </div>

        <?php
            // 個資管理
            }else if(isset($_GET['p']) && $_GET['p'] === 'edit_own'){
        ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mx-auto" style="max-width: 600px;">
            <div class="card-header bg-secondary text-white p-3 text-center">
                <h5 class="m-0 fw-bold"><i class="bi bi-shield-lock me-2"></i>個人資料管理</h5>
            </div>
            <div class="card-body p-4 text-start">
                <form method="post" action="./save_edit_teacher">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($data['id']) ?>">
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold">登入帳號</label>
                        <input type="text" class="form-control px-3 py-2 rounded-3 bg-light border-0" id="username" name="username" value="<?= htmlspecialchars($data['username']) ?>">
                    </div>
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold">更新密碼</label>
                        <div class="input-group">
                            <input type="password" 
                                class="form-control px-3 py-2 rounded-start-3 bg-light border-0" 
                                id="password" 
                                name="password" 
                                value="<?= htmlspecialchars($data['password']) ?>">
                            
                            <button type="button" 
                                    class="btn btn-light border-0 rounded-end-3 px-3 toggle-password-btn" 
                                    style="background-color: #f8f9fa;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">若不修改請保持原狀</small>
                    </div>

                    <script>
                    $(function() {
                        $('.toggle-password-btn').on('click', function() {
                            const $input = $('#password');
                            const isPass = $input.attr('type') === 'password';
                            
                            // 切換類型
                            $input.attr('type', isPass ? 'text' : 'password');
                            
                            // 切換圖示
                            $(this).find('i').toggleClass('bi-eye bi-eye-slash');
                        });
                    });
                    </script>
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-dark px-5 py-2 rounded-pill fw-bold w-100">保存更新資料</button>
                    </div>
                </form>
            </div>
        </div>

        <?php
            // 題目管理
            }else if(isset($_GET['p']) && $_GET['p'] === 'question'){
        ?>
        <div class="col-lg-12">
            <h4 class="fw-bold mb-4"><i class="bi bi-file-earmark-text me-2"></i>題目預覽管理</h4>
            <div class="row g-4">
                <?php
                    foreach ($showExamList as $data) {
                        $questionSet = $data['questionSet'];
                        echo '<div class="col-12"><div class="que-op-card bg-white shadow-sm rounded-4 p-4 border-start border-success border-5">';
                        echo '<div class="d-flex justify-content-between mb-3 align-items-start">';
                        echo '<div class="fs-5 fw-bold text-dark pe-3">'.htmlspecialchars($questionSet['content']).'</div>';
                        if(isset($questionSet['que_type'])){
                            echo '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small flex-shrink-0">'.htmlspecialchars($questionSet['que_type']).'</span>';
                        }
                        echo '</div>';
                        
                        if (!empty($questionSet['image_url'])) {
                            echo '<div class="mb-3 text-center bg-light rounded-3 p-2"><img src="../../' . htmlspecialchars($questionSet['image_url']) . '" class="img-fluid rounded-3" style="max-height: 250px;" alt="題目圖片" /></div>';
                        }

                        echo '<div class="question-items mt-4">';
                        foreach ($data['questions'] as $questionData) {
                            $question = $questionData['question'];
                            echo '<div class="p-3 bg-light rounded-3 mb-3">';
                            echo '<div class="fw-bold text-dark mb-3"><span class="text-success me-2">●</span>'.htmlspecialchars($question['question_text']).' <span class="text-danger ms-2 small">[解答：'.htmlspecialchars($question['correct_option']).']</span></div>';
                            echo '<div class="row g-2">';
                            foreach ($questionData['options'] as $option) {
                                echo '<div class="col-md-6"><div class="option-item small bg-white p-2 px-3 rounded-2 border">';
                                echo '<span class="fw-bold text-primary me-2">'.htmlspecialchars($option['option_letter']).'</span>';
                                echo '<span>' . htmlspecialchars($option['option_text']) . '</span>';
                                if (!empty($option['option_image'])) {
                                    echo '<img src="../../' . htmlspecialchars($option['option_image']) . '" class="d-block mt-2 img-thumbnail" style="max-height: 80px;" />';
                                }
                                echo '</div></div>';
                            }
                            echo '</div></div>';
                        }
                        echo '</div></div></div>';
                    }
                ?>
            </div>
        </div>

        <?php
            // 成績管理 (預設)
            }else {
        ?>
        <div class="mb-4 d-flex justify-content-between align-items-center text-start">
            <h4 class="fw-bold m-0 text-dark"><i class="bi bi-clipboard-data me-2 text-success"></i>成績管理總覽</h4>
        </div>
        
        <div class="table-container shadow-sm bg-white p-3 rounded-4">
            <div class="table-responsive text-start">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4">學生姓名</th>
                            <th class="border-0">學生班級</th>
                            <th class="border-0 text-center">總成績</th>
                            <th class="border-0 ps-4">作答時間</th>
                            <th class="border-0 ps-4">查看</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if (!empty($showExamList)) {
                                foreach ($showExamList as $row) {
                                    echo '<tr>
                                        <td class="ps-4"><div class="d-flex align-items-center"><i class="bi bi-person-circle text-secondary me-2"></i>'.htmlspecialchars($row['name']).'</div></td>
                                        <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 fw-normal">'.htmlspecialchars($row['classname']).'</span></td>
                                        <td class="text-center"><span class="h5 m-0 fw-bold text-success">'.htmlspecialchars($row['score']).'</span> <small class="text-muted">分</small></td>
                                        <td class="ps-4 text-muted small">'.htmlspecialchars($row['quiztime']).'</td>
                                        <td class="text-center">
                                            <a class="text-decoration-none view-link" href="./readquiz?examid=' . $row['exam_id'] . '">
                                                <i class="bi bi-eye me-1"></i>查看
                                            </a>
                                        </td>
                                    </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center py-5 text-muted">目前尚無作答紀錄資料</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script type="text/javascript">
        function addxlsxdata(e){
            const fileInput = e.files[0];
            if (!fileInput) {
                alert("請選擇檔案！");
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                const data = new Uint8Array(event.target.result);
                const workbook = XLSX.read(data, { type: "array" });
                const sheetName = workbook.SheetNames[0];
                const sheet = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], { header: 2 });
                console.log({ data: {sheet} ,x: 'xlsx'});
                
                // 發送資料到伺服器
                $.ajax({
                    url: './save_add_student',
                    method: "POST",
                    data: { data: JSON.stringify({sheet}),x:'xlsx' },
                    success: function(html) {
                        if (html.includes("加入學生資料成功!")) {
                            alert("加入學生資料成功!");
                            setTimeout(function() {
                                window.location.href = "./teacher?p=student";  // 導回首頁
                            }, 2000);  // 2秒延遲
                        }else{
                            alert("加入學生資料失敗!");
                        }
                    },
                    error: function(error) {
                        console.log("失敗! 資料上傳失敗", error.responseText);
                    }
                });
            };
            reader.readAsArrayBuffer(fileInput);
        }
</script>
<?php
        }
    }
?>
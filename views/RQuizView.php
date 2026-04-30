<?php
    class RQuizView {
        public function render($examData,$data) {
?>
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', 'Microsoft JhengHei', sans-serif;
        line-height: 1.6;
    }
    .main-wrapper {
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        padding: 40px;
        margin-top: 30px;
        margin-bottom: 50px;
    }
    /* 學生資訊欄 - 極簡化 */
    .info-header {
        border-bottom: 2px solid #f1f1f1;
        padding-bottom: 20px;
        margin-bottom: 40px;
    }
    .info-tag {
        background: #f1f3f5;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        color: #495057;
    }
    /* 大題容器 */
    .que_op {
        list-style: none;
        padding-left: 25px;
        border-left: 4px solid #dee2e6;
        margin-bottom: 60px;
        transition: border-color 0.3s;
    }
    .que_op:hover { border-left-color: #28a745; }
    
    .que_op .text {
        font-size: 1.15rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
    }
    /* 小題樣式 */
    .quelist_i {
        list-style: none;
        margin: 30px 0;
        padding-left: 0;
    }
    .ques {
        font-size: 1.05rem;
        margin-bottom: 15px;
        color: #343a40;
    }
    /* 選項樣式 - 修正對齊關鍵 */
    .option ol { list-style: none; padding-left: 0; }
    .option label {
        display: flex;
        align-items: flex-start; /* 對齊第一行文字 */
        padding: 12px 16px;
        margin-bottom: 8px;
        cursor: pointer;
        border-radius: 10px;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .option label:hover { background-color: #f1f3f5; }
    
    .option input[type="radio"] {
        margin-top: 5px; /* 根據文字大小微調按鈕垂直位置 */
        margin-right: 15px;
        flex-shrink: 0;
        transform: scale(1.2);
    }
    .option span { font-size: 1rem; color: #495057; }

    /* 圖片美化 */
    img {
        border-radius: 8px;
        margin: 15px 0;
        max-width: 100%;
        border: 1px solid #eee;
    }

    /* 底部操作區 */
    .action-bar {
        position: sticky;
        bottom: 0;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        padding: 20px;
        margin: 0 -40px -40px -40px;
        border-top: 1px solid #eee;
        border-bottom-left-radius: 16px;
        border-bottom-right-radius: 16px;
        display: flex;
        justify-content: center;
        gap: 15px;
    }

    /* 狀態標示 - 避免大面積色塊，改用邊框與文字色 */
    .user-error {
        border: 1px solid #ffa8a8 !important;
        background-color: #fff5f5 !important;
        color: #e03131 !important;
    }
    .correct-answer {
        border: 1px solid #d8f5a2 !important;
        background-color: #f4fce3 !important;
        color: #2b8a3e !important;
        font-weight: bold;
    }
</style>

<div class="container">
    <div class="main-wrapper">
        <div class="info-header d-flex justify-content-between align-items-center">
            <h2 class="fw-bold m-0 text-success"><i class="bi bi-journal-check me-2"></i>測驗卷</h2>
            <div class="d-flex gap-2">
                <span class="info-tag">姓名：<?php echo $data['name'] ?? ""; ?></span>
                <span class="info-tag">學號：<?php echo $data['stdId'] ?? ""; ?></span>
                <span class="info-tag">題數：共<?php print_r(count($examData));?>題</span>
                <input type="hidden" value="<?php echo $quetype ?? "";?>">
            </div>
        </div>

        <form method="POST" action="./check_exam_answer" id="examform">
            <ul class="question_list list-unstyled">
                <?php foreach ($examData as $index => $data): 
                    $questionSet = $data['questionSet']; ?>
                    <li class="que_op" id="question-block-<?= $index+1;?>">
                        <div class="d-flex justify-content-end"><span class="info-tag">ch<?php echo htmlspecialchars($questionSet['que_type']); ?></span></div>
                        <div class="text">
                            <?php echo htmlspecialchars($questionSet['content']); ?>
                            <?php if (!empty($questionSet['image_url'])): ?>
                                <img src="../../<?php echo htmlspecialchars($questionSet['image_url']); ?>" class="shadow-sm">
                            <?php endif; ?>
                        </div>

                        <ol type="1" class="ps-0">
                            <?php foreach ($data['questions'] as $questionData): 
                                $question = $questionData['question']; ?>
                                <li class="quelist_i">
                                    <div class="ques fw-bold"><?php echo htmlspecialchars($question['question_text']); ?></div>
                                    <div class="option">
                                        <ol>
                                            <?php foreach ($questionData['options'] as $option): ?>
                                                <li>
                                                    <label>
                                                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="<?php echo htmlspecialchars($option['option_letter']); ?>" required>
                                                        <span><?php echo htmlspecialchars($option['option_letter']) . '. ' . htmlspecialchars($option['option_text']); ?></span>
                                                        <?php if (!empty($option['option_image'])): ?>
                                                            <img src="../../<?php echo htmlspecialchars($option['option_image']); ?>" style="max-width:200px">
                                                        <?php endif; ?>
                                                    </label>
                                                </li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="action-bar">
                <button type="button" class="btn btn-outline-danger btn-lg px-4" id="subAns1" onclick="location.href='./requiz?r=1';">返回</button>
                <button type="button" class="btn btn-dark shadow-sm rounded-pill px-4 py-2 position-fixed" 
                        style="bottom: 80px; right: 20px; z-index: 1050;" 
                        data-bs-toggle="modal" data-bs-target="#jumpToQuestionModal">
                    <i class="bi bi-list-ol me-2"></i>跳至題目
                </button>
            </div>
        </form>

    </div>
</div>
<div class="modal fade" id="jumpToQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-grid-3x3-gap me-2"></i>題目跳轉</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-wrap justify-content-start gap-2" id="question-navigator">
                    <?php 
                        // 假設 $examData 是包含所有大題的陣列
                        $qIndex = 1;
                        foreach ($examData as $data) {
                            foreach ($data['questions'] as $questionData) {
                                // 為每個題目產生一個按鈕
                                echo '<button type="button" 
                                        class="btn btn-outline-secondary rounded-circle nav-q-btn" 
                                        style="width: 45px; height: 45px;" 
                                        onclick="scrollToQuestion(' . $qIndex . ')">' . $qIndex . '</button>';
                                $qIndex++;
                            }
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function scrollToQuestion(index) {
        const targetId = 'question-block-' + index;
        const element = document.getElementById(targetId);
        
        if (element) {
            // 關閉彈窗
            const modalEl = document.getElementById('jumpToQuestionModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // 平滑滾動到目標，並留一點頂部間距 (offset)
            const headerOffset = 100;
            const elementPosition = element.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });

            // 視覺特效：閃爍一下目標題目
            element.style.transition = 'background-color 0.5s';
            element.style.backgroundColor = '#fff9db';
            setTimeout(() => {
                element.style.backgroundColor = 'transparent';
            }, 1000);
        }
    }

    $(document).ready(function() {
        // 監聽所有 radio 的變化
        $('input[type="radio"]').on('change', function() {
            const name = $(this).attr('name');
            const match = name.match(/\[(\d+)\]/);
            
            if (match) {
                updateNavigatorStatus();
            }
        });
    });

    function updateNavigatorStatus() {
        $('.quelist_i').each(function(index) {
            const hasValue = $(this).find('input[type="radio"]:checked').length > 0;
            const btn = $('.nav-q-btn').eq(index);
            if (hasValue) {
                btn.removeClass('btn-outline-secondary').addClass('btn-success text-white');
            }
        });
    }

    // 完全保留原有的 JS 邏輯，僅優化了對 DOM 的選取
    document.addEventListener('contextmenu', e => e.preventDefault());
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey && e.key === 'c') || e.key === 'F12') e.preventDefault();
    });

    document.querySelectorAll('input[type="radio"]').forEach(el => el.disabled = true);

    // 標示邏輯 (沿用您的優化版)
    const dataList = Array.isArray(errorQuestions) ? errorQuestions : [errorQuestions];
    dataList.forEach(group => {
        if(!group) return;
        Object.entries(group).forEach(([gid, qData]) => {
            Object.entries(qData).forEach(([qid, res]) => {
                if(res.user_answer && res.correct_answer){
                    const userEl = document.querySelector(`[name="answers[${qid}]"][value="${res.user_answer}"]`);
                    const corrEl = document.querySelector(`[name="answers[${qid}]"][value="${res.correct_answer}"]`);
                    userEl?.closest("li")?.classList.add("user-error");
                    if(userEl) { userEl.disabled = false; userEl.checked = true; }
                    corrEl?.closest("li")?.classList.add("correct-answer");
                }
                if(res.c_user_answer){
                    const corrEl = document.querySelector(`[name="answers[${qid}]"][value="${res.c_user_answer}"]`);
                    corrEl?.closest("li")?.classList.add("correct-answer");
                    if(corrEl) { corrEl.disabled = false; corrEl.checked = true; }
                }
            });
        });
    });
</script>
<?php
        }
    }
?>
<?php
    class IndexView {
        public function render($data,$type) {
            $type = $type=='teacher'?'老師':'學生';
?>
<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-family: 'Segoe UI', 'Microsoft JhengHei', sans-serif;
    }
    .main-card {
        max-width: 400px;
        border: none;
        border-radius: 20px !important;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
    }
    .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border: 1px solid #ddd;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.15);
        border-color: #28a745;
    }
    .btn-login {
        border-radius: 8px;
        padding: 8px 25px;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-login:hover {
        background-color: #28a745;
        color: white;
        transform: translateY(-1px);
    }
    .btn-rest {
        border-radius: 8px;
        padding: 8px 25px;
        font-weight: 600;
        transition: 0.3s;
    }
    .entrance-link {
        font-size: 0.9rem;
        color: #666;
    }
    .entrance-link a {
        color: #007bff;
        font-weight: bold;
        text-decoration: none;
    }
    .entrance-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="container rounded-1">
    <div class="main d-flex align-items-center" style="min-height: 100vh;">
        <form class="m-auto shadow rounded p-4 bg-light main-card" login-form method="post" action="<?php $gander = $type ==='老師'?'teacher':'student';echo $gander;?>_login_check">
            <div class="mb-4">
                <h1 class="text-center p-2 fw-bolder" style="color: #333;"><?php echo $type;?>登入</h1>
                <hr style="width: 50px; border: 2px solid #28a745; margin: 0 auto;">
            </div>
            
            <div class="mb-3 row">
                <label class="col-3 col-form-label fw-bold text-end"><?php $username = $type ==='老師'?$data['form_username']:$data['form_stdId'];echo $username;?>：</label>
                <div class="col-9">
                    <input type="text" name="username" class="form-control" placeholder="請輸入<?php $username = $type ==='老師'?$data['form_username']:$data['form_stdId']; echo $username; ?>!" required>
                </div>
            </div>
            
            <div class="mb-4 row">
                <label class="col-3 col-form-label fw-bold text-end">
                    <?php $pass_label = ($type === '老師' ? $data['form_password'] : $data['form_stdname']); echo $pass_label; ?>：
                </label>
                <div class="col-9">
                    <div class="input-group">
                        <input type="<?php echo ($type === '老師' ? 'password' : 'text'); ?>" 
                            id="input-pass-<?php echo $type; ?>" 
                            name="password" 
                            class="form-control" 
                            placeholder="請輸入<?php echo $pass_label; ?>!" 
                            required>
                        
                        <?php if($type === '老師'): ?>
                            <button type="button" class="btn btn-outline-secondary toggle-password" 
                                    data-target="#input-pass-<?php echo $type; ?>">
                                <i class="bi bi-eye"></i>
                            </button>
                        <?php endif; ?>
                    </div>

                    <?php if($type === '老師'): ?>
                    <script>
                        $(function() {
                            $('.toggle-password').on('click', function () {
                                const targetSelector = $(this).data('target');
                                const $input = $(targetSelector);
                                const isPassword = $input.attr('type') === 'password';
                                
                                // 切換型態
                                $input.attr('type', isPassword ? 'text' : 'password');

                                // 切換 icon 並調整按鈕樣式
                                $(this).html(isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>');
                                $(this).toggleClass('btn-outline-secondary', !isPassword).toggleClass('btn-secondary', isPassword);
                            });
                        });
                    </script>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mb-3 d-flex justify-content-center">
                <div class="d-inline p-2">
                    <button type="submit" class="btn btn-outline-success btn-login">登入</button>
                </div>
                <div class="d-inline p-2">
                    <button type="reset" class="btn btn-outline-danger btn-rest">重置</button>
                </div>      
            </div>
            
            <div class="mb-2 d-flex justify-content-center entrance-link">
                <span><?php $gander = $type ==='老師'?'學生':'老師';echo $gander;?>入口：</span>
                <a href="./index<?php $gander = $type ==='老師'?'':'?t=teacher';echo $gander;?>">立即點此</a>
            </div>
        </form>
    </div>
</div>
<?php
        }
    }
?>
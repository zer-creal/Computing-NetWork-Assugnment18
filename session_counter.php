<?php
// (2) session_counter.php - 利用 PHP $_SESSION 实现访问计数器
session_start();

// 初始化或更新访问计数
if (!isset($_SESSION['visit_count'])) {
    $_SESSION['visit_count'] = 1;
    $message = '欢迎首次来访！';
} else {
    $_SESSION['visit_count']++;
    $last_time = isset($_SESSION['last_visit']) ? $_SESSION['last_visit'] : '未知';
    $message = "这是您第 {$_SESSION['visit_count']} 次访问，上次访问时间为 {$last_time}";
}

// 记录本次访问时间
$_SESSION['last_visit'] = date('Y-m-d H:i:s');

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Session 计数器</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .welcome { background: #e3f2fd; padding: 20px; border-radius: 8px; font-size: 18px; }
        .note { background: #fff3e0; padding: 15px; border-radius: 5px; margin-top: 20px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Session 访问计数器</h1>
    <div class="welcome">
        <p><?php echo $message; ?></p>
    </div>
    <div class="note">
        <h3>观察要点：</h3>
        <ul>
            <li>F12 → Application/Storage → Cookies，观察 <code>PHPSESSID</code></li>
            <li>响应头 <code>Set-Cookie</code> 包含 <code>PHPSESSID</code></li>
            <li>关闭浏览器再打开，计数器重置（会话级 Session）</li>
            <li>当前 Session ID: <code><?php echo session_id(); ?></code></li>
            <li>当前 Session 数据:</li>
        </ul>
        <pre><?php print_r($_SESSION); ?></pre>
    </div>
</body>
</html>
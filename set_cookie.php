<?php
// (1) set_cookie.php - 设置会话级 Cookie（不设置过期时间）
// 会话级 Cookie：浏览器关闭后自动删除

setcookie('session_token', 'abc123'); // 不设置过期时间 = 会话级 Cookie

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>Set Cookie Demo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .info { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>会话级 Cookie 已设置</h1>
    <div class="info">
        <p>Cookie 名称: <code>session_token</code></p>
        <p>Cookie 值: <code>abc123</code></p>
        <p>过期时间: <strong>未设置（会话级，关闭浏览器后自动删除）</strong></p>
    </div>
    <p>请打开 F12 → Application/Storage → Cookies 查看。</p>
    <p>刷新页面后，观察请求头中 <code>Cookie: session_token=abc123</code> 字段。</p>
    <hr>
    <h2>当前请求中的 Cookie：</h2>
    <pre><?php echo isset($_COOKIE['session_token']) ? 'session_token = ' . htmlspecialchars($_COOKIE['session_token']) : '（无 Cookie）'; ?></pre>
</body>
</html>
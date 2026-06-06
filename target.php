<?php
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '（无 Referer — 可能是直接访问或 Referrer-Policy 被设为 no-referrer）';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>重定向目标页面</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; }
        .success { background: #e8f5e9; padding: 20px; border-radius: 8px; }
        .referer-box { background: #fff3e0; padding: 15px; border-radius: 5px; margin-top: 15px; word-break: break-all; }
        .note { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>你被重定向到了这里！</h1>
    <div class="success">
        <p>状态码: <strong>302 Found</strong>（由 redirect.php 发出）</p>
    </div>
    <div class="referer-box">
        <h3>原始请求头中的 Referer：</h3>
        <p style="font-size: 16px;"><code><?php echo htmlspecialchars($referer); ?></code></p>
    </div>
    <div class="note">
        <h3>思考：如何避免在重定向时泄露 Referer？</h3>
        <ol>
            <li>在 <code>redirect.php</code> 中设置响应头 <code>Referrer-Policy: no-referrer</code></li>
            <li>在 HTML 中用 <code>&lt;meta name="referrer" content="no-referrer"&gt;</code></li>
            <li>使用 HTTPS → HTTP 降级时浏览器默认不发送 Referer</li>
            <li>使用 <code>rel="noreferrer"</code> 属性在 <code>&lt;a&gt;</code> 标签中</li>
        </ol>
    </div>
</body>
</html>
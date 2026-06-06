<?php
// (6) ua.php - 读取 User-Agent、Accept-Language、Accept-Encoding
// 检测 UA 是否包含 curl

$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '（无法获取 User-Agent）';
$accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '（未提供）';
$accept_encoding = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '（未提供）';

$is_curl = (stripos($ua, 'curl') !== false);

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>User-Agent 检测</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; }
        .ua-box { background: #263238; color: #aed581; padding: 20px; border-radius: 8px; font-family: 'Courier New', monospace; word-break: break-all; }
        .curl-detect { background: <?php echo $is_curl ? '#ff5722' : '#4caf50'; ?>; color: white; padding: 20px; border-radius: 8px; text-align: center; font-size: 18px; margin: 15px 0; }
        .info { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .section { margin: 15px 0; }
        h3 { color: #333; border-bottom: 2px solid #2196f3; padding-bottom: 5px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>浏览器标识检测</h1>

    <?php if ($is_curl): ?>
    <div class="curl-detect">
        <h2>机器人侦探，欢迎使用命令行</h2>
    </div>
    <?php endif; ?>

    <div class="section">
        <h3>你的浏览器是：</h3>
        <div class="ua-box"><?php echo htmlspecialchars($ua); ?></div>
    </div>

    <div class="section">
        <h3>Accept-Language（语言偏好）：</h3>
        <div class="info"><?php echo htmlspecialchars($accept_language); ?></div>
    </div>

    <div class="section">
        <h3>Accept-Encoding（压缩方式）：</h3>
        <div class="info"><?php echo htmlspecialchars($accept_encoding); ?></div>
    </div>

    <div class="section">
        <h3>所有请求头：</h3>
        <pre style="background:#263238;color:#aed581;padding:15px;border-radius:5px;overflow-x:auto;"><?php
foreach (getallheaders() as $name => $value) {
    echo htmlspecialchars("$name: $value") . "\n";
}
?></pre>
    </div>

    <div class="info">
        <p><strong>提示：</strong>在 F12 → More tools → Network conditions 中可模拟手机 UA。</p>
        <p>或用 <code>curl <?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?></code> 测试。</p>
    </div>
</body>
</html>
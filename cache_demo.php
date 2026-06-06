<?php
// (5) cache_demo.php - 浏览器缓存演示
// 设置 Cache-Control: max-age=30，浏览器缓存 30 秒

header('Cache-Control: max-age=30, public');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 30) . ' GMT');

$current_time = date('Y-m-d H:i:s');
$timestamp = time();

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>缓存演示</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .time-display { background: #263238; color: #76ff03; padding: 30px; border-radius: 10px; text-align: center; font-size: 24px; font-family: 'Courier New', monospace; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .cache-hit { color: #ff9800; font-weight: bold; }
        .note { background: #fff3e0; padding: 15px; border-radius: 5px; margin-top: 15px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>浏览器缓存演示（max-age=30秒）</h1>
    <div class="time-display">
        <div>服务器时间戳</div>
        <div style="font-size: 48px; margin: 10px 0;"><?php echo $timestamp; ?></div>
        <div><?php echo $current_time; ?></div>
    </div>
    <div class="info">
        <h3>观察要点：</h3>
        <ul>
            <li><strong>30 秒内刷新</strong>：F12 Network → Size 列显示 <span class="cache-hit">"(memory cache)"</span> 或 <span class="cache-hit">"(disk cache)"</span>，时间戳不变</li>
            <li><strong>30 秒后刷新</strong>：重新请求服务器，时间戳更新</li>
            <li>响应头 <code>Cache-Control: max-age=30</code></li>
        </ul>
    </div>
    <div class="note">
        <p>页面生成时间: <code><?php echo $current_time; ?></code></p>
        <p>此页面将于 <code><?php echo date('Y-m-d H:i:s', time() + 30); ?></code> 过期</p>
    </div>
</body>
</html>
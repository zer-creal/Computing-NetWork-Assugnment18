<?php
// (7) statuscode.php - 生成不同的 HTTP 状态码
// 通过 ?code=xxx 参数指定状态码

$allowed_codes = [200, 204, 301, 302, 400, 403, 404, 500, 502, 503];
$code = isset($_GET['code']) ? intval($_GET['code']) : 200;

if (!in_array($code, $allowed_codes)) {
    $code = 200;
}

// 状态码描述映射
$descriptions = [
    200 => 'OK - 请求成功',
    204 => 'No Content - 成功但无响应体',
    301 => 'Moved Permanently - 永久重定向',
    302 => 'Found - 临时重定向',
    400 => 'Bad Request - 请求错误',
    403 => 'Forbidden - 禁止访问',
    404 => 'Not Found - 页面不存在',
    500 => 'Internal Server Error - 服务器内部错误',
    502 => 'Bad Gateway - 网关错误',
    503 => 'Service Unavailable - 服务不可用',
];

// 特殊处理：301/302 重定向
if ($code === 301) {
    header('Location: target.php', true, 301);
    exit;
}
if ($code === 302) {
    header('Location: target.php', true, 302);
    exit;
}

// 设置状态码
http_response_code($code);

// 204 不输出内容
if ($code === 204) {
    exit;
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>HTTP 状态码: <?php echo $code; ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; }
        .status-badge {
            display: inline-block; padding: 15px 30px; border-radius: 10px;
            font-size: 28px; font-weight: bold; color: white; margin: 10px 0;
        }
        .s200 { background: #4caf50; }
        .s400 { background: #ff9800; }
        .s403 { background: #f44336; }
        .s404 { background: #ff5722; }
        .s500, .s502, .s503 { background: #9c27b0; }
        .desc { font-size: 18px; margin: 10px 0; color: #555; }
        .nav { background: #f5f5f5; padding: 15px; border-radius: 8px; margin-top: 30px; }
        .nav a { display: inline-block; margin: 5px 8px; padding: 8px 15px; background: #2196f3; color: white; text-decoration: none; border-radius: 5px; }
        .nav a:hover { background: #1976d2; }
        .nav a.active { background: #0d47a1; }
        pre { background: #263238; color: #aed581; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>HTTP 状态码测试</h1>

    <?php
    $class = 's200';
    if ($code >= 500) $class = 's500';
    elseif ($code >= 400) $class = 's' . $code;
    ?>
    <div class="status-badge <?php echo $class; ?>">
        <?php echo $code; ?>
    </div>
    <div class="desc"><?php echo $descriptions[$code]; ?></div>

    <p>打开 F12 → Network，查看响应状态码。</p>

    <div class="nav">
        <h3>选择状态码：</h3>
        <?php foreach ($allowed_codes as $c): ?>
        <a href="?code=<?php echo $c; ?>" class="<?php echo ($c === $code) ? 'active' : ''; ?>">
            <?php echo $c; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 20px;">
        <h3>当前响应头信息：</h3>
        <pre><?php
echo "HTTP Status: " . http_response_code() . " " . ($descriptions[$code] ?? '') . "\n";
$headers = headers_list();
foreach ($headers as $h) {
    echo htmlspecialchars($h) . "\n";
}
?></pre>
    </div>
</body>
</html>
<?php
$method = $_SERVER['REQUEST_METHOD'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>HTTP Method Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; }
        .method-badge {
            display: inline-block; padding: 8px 20px; border-radius: 20px;
            font-weight: bold; font-size: 18px; margin: 10px 0;
        }
        .GET { background: #4caf50; color: white; }
        .POST { background: #2196f3; color: white; }
        .PUT { background: #ff9800; color: white; }
        .OTHER { background: #f44336; color: white; }
        .section { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #263238; color: #aed581; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        textarea { width: 100%; height: 80px; margin: 5px 0; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; border: none; border-radius: 5px; color: white; font-size: 14px; }
        .btn-post { background: #2196f3; }
        .btn-put { background: #ff9800; }
    </style>
</head>
<body>
    <h1>HTTP 请求方法测试</h1>

    <div class="method-badge <?php echo $method; ?>">
        当前请求方法：<?php echo $method; ?>
    </div>

    <div class="section">
        <h3>GET 参数（$_GET）：</h3>
        <pre><?php echo empty($_GET) ? '（无 GET 参数）' : print_r($_GET, true); ?></pre>
    </div>

    <div class="section">
        <h3>POST 参数（$_POST）：</h3>
        <pre><?php echo empty($_POST) ? '（无 POST 参数）' : print_r($_POST, true); ?></pre>
    </div>

    <div class="section">
        <h3>PUT 数据（原始请求体）：</h3>
        <pre><?php
$put_data = file_get_contents('php://input');
echo empty($put_data) ? '（无 PUT 数据）' : htmlspecialchars($put_data);
?></pre>
    </div>

    <hr>
    <h2>测试工具</h2>

    <h3>GET 请求（直接在 URL 加参数）：</h3>
    <p><a href="?name=test&value=123">点击测试 GET: ?name=test&value=123</a></p>

    <h3>POST 请求（表单提交）：</h3>
    <form method="POST">
        <input type="text" name="username" placeholder="用户名" value="testuser"><br>
        <input type="text" name="email" placeholder="邮箱" value="test@example.com"><br>
        <button type="submit" class="btn-post">提交 POST 请求</button>
    </form>

    <h3>使用 curl 测试 PUT：</h3>
    <pre>curl -X PUT -d "key=value&action=update" <?php
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
echo $url;
?></pre>

    <div class="section">
        <h3>所有请求头信息：</h3>
        <pre><?php
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
?></pre>
    </div>
</body>
</html>
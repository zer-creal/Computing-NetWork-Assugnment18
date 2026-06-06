<?php
$type = isset($_GET['type']) ? $_GET['type'] : 'inline';

$content = "========================================\n";
$content .= "    绝密情报 - CONFIDENTIAL REPORT\n";
$content .= "========================================\n\n";
$content .= "文件生成时间: " . date('Y-m-d H:i:s') . "\n";
$content .= "文档编号:  DOC-" . date('YmdHis') . "\n";
$content .= "安全级别:  最高机密\n\n";
$content .= "这是绝密情报，请在浏览器内直接阅读。\n";
$content .= "This is a confidential document.\n\n";
$content .= "========================================\n";
$content .= "    内部文件 - 请勿外传\n";
$content .= "========================================\n";

$filename = 'report.txt';

header('Content-Type: text/plain; charset=utf-8');

if ($type === 'attachment') {
    header('Content-Disposition: attachment; filename="confidential.txt"');
    header('Content-Length: ' . strlen($content));
    echo $content;
    exit;
} else {
    header('Content-Disposition: inline; filename="report.txt"');
    header('Content-Length: ' . strlen($content));
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>文件预览 - <?php echo $filename; ?></title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
            .header { background: #2196f3; color: white; padding: 15px 20px; border-radius: 8px 8px 0 0; }
            .content { background: white; padding: 25px; border-radius: 0 0 8px 8px; font-family: 'Courier New', monospace; white-space: pre-wrap; border: 1px solid #ddd; }
            .mode-badge { display: inline-block; padding: 5px 12px; border-radius: 15px; font-size: 12px; font-weight: bold; }
            .inline { background: #4caf50; color: white; }
            .attachment { background: #ff9800; color: white; }
            .note { background: #fff3e0; padding: 15px; border-radius: 5px; margin-top: 20px; }
            .nav { margin-top: 20px; }
            .nav a { display: inline-block; margin: 5px; padding: 10px 20px; border-radius: 5px; text-decoration: none; color: white; }
            .btn-inline { background: #4caf50; }
            .btn-attachment { background: #ff9800; }
            code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>文件预览模式</h2>
            <span class="mode-badge inline">Content-Disposition: inline</span>
        </div>
        <div class="content"><?php echo htmlspecialchars($content); ?></div>

        <div class="note">
            <h3>观察要点：</h3>
            <ul>
                <li>当前模式：<strong>inline</strong> — 浏览器直接显示内容</li>
                <li>响应头：<code>Content-Disposition: inline; filename="report.txt"</code></li>
                <li>F12 → Network → 查看响应头中的 <code>Content-Disposition</code> 字段</li>
            </ul>
        </div>

        <div class="nav">
            <h3>切换模式：</h3>
            <a href="?type=inline" class="btn-inline">Inline 模式（内联显示）</a>
            <a href="?type=attachment" class="btn-attachment">Attachment 模式（强制下载）</a>
        </div>
    </body>
    </html>
    <?php
}
?>
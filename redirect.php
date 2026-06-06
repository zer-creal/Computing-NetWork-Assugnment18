<?php
// (4) redirect.php - 302 重定向到 target.php
// 设置 Referrer-Policy: no-referrer 避免泄露 Referer

header('Referrer-Policy: no-referrer');

header('Location: target.php', true, 302);
exit;
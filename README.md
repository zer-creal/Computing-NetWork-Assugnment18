# Computing-Network-Assignment18

计算机网络课程作业 —— LNMP 环境下 HTTP 头部模拟实验。

## 环境

- Windows + PHP 8.3.31 内置开发服务器
- 启动命令：`php -S localhost:8080`

## 文件列表

| 页面 | 功能 |
|------|------|
| `set_cookie.php` | 设置会话级 Cookie `session_token=abc123` |
| `session_counter.php` | Session 访问计数器，首次显示"欢迎首次来访！" |
| `method_test.php` | 同时支持 GET / POST / PUT 请求 |
| `redirect.php` | 302 重定向到 target.php，带 Referrer-Policy: no-referrer |
| `target.php` | 重定向目标页，显示 Referer 信息 |
| `cache_demo.php` | 设置 Cache-Control: max-age=30，演示浏览器缓存 |
| `ua.php` | 读取 User-Agent / Accept-Language / Accept-Encoding，检测 curl |
| `statuscode.php` | 生成 200/204/302/400/403/404/500/502/503 等状态码 |
| `down.php` | Content-Disposition inline / attachment 下载控制 |

## 观察方法

用浏览器访问 `http://localhost:8080/xxx.php`，按 F12 打开开发者工具，在 Network / Application 标签中观察 HTTP 头部行为。

# tinyhttpd 个人笔记

这篇教程是我在阅读 tinyhttpd 源码之后整理的学习笔记。tinyhttpd 是一个非常精简的 HTTP 服务器实现，代码总共不到 600 行，却完整覆盖了 Web 服务器最核心的功能：TCP 监听、请求解析、静态文件服务、CGI 动态执行以及多线程并发。适合用来理解 HTTP 协议、Unix 网络编程和进程间通信的基础知识。

> 本项目使用了ai的力量，先通过ai的方法操作一遍然后才制作的这篇经验教程把

## 一、环境搭建与编译运行

首先要把项目跑起来，这样边运行边调试，理解会更深刻。

### 1.1 获取源码

我使用的是 GitHub 上已被 Linux 适配过的版本（原始 SourceForge 版本对 Linux 不太友好）：

```bash
git clone https://github.com/EZLippi/Tinyhttpd.git
cd Tinyhttpd
```

项目结构很简单：

- `httpd.c` —— 服务器主程序
- `simpleclient.c` —— 一个测试用的客户端
- `htdocs/` —— 网站根目录，里面有几个 `.html` 和 `.cgi` 文件
- `Makefile`

### 1.2 安装 Perl CGI 模块

tinyhttpd 自带的 CGI 示例是用 Perl 写的，需要安装 Perl 的 CGI 模块，否则访问时会有 500 错误。

```bash
# Ubuntu / Debian
sudo apt install libcgi-pm-perl

# 或者用 cpan
perl -MCPAN -e 'install CGI'
```

### 1.3 修改 CGI 脚本的 shebang 路径

进入 `htdocs` 目录，把 `color.cgi` 和 `date.cgi` 的第一行从 `/usr/local/bin/perl` 改成系统实际的 Perl 路径（用 `which perl` 查看），并赋予执行权限：

```bash
cd htdocs
sed -i 's|/usr/local/bin/perl|/usr/bin/perl|' *.cgi
chmod 755 *.cgi
```

### 1.4 编译

直接 `make` 可能会遇到链接错误，原因是 Makefile 中 `LIBS` 放在了目标文件前面。我手动修改了一下 Makefile：

```makefile
httpd: httpd.c
    gcc -g -W -Wall -o httpd httpd.c -lpthread
```

或者直接用命令：

```bash
gcc -o httpd httpd.c -lpthread
```

### 1.5 运行

```bash
./httpd
```

默认监听 4000 端口。浏览器访问 `http://localhost:4000`，看到一个颜色选择的页面，输入 `red/green/blue` 提交，页面背景颜色会变化 —— CGI 正常工作。

>上诉编译方法有一部分是ai教学的，可能不一定与其他设备相兼容

## 二、核心模块功能说明

我将源码中的函数分成五个核心模块，逐一说明。

### 模块总览

| 模块 | 函数 | 作用 |
| --- | --- | --- |
| 服务器初始化 | `startup()` | 创建 socket，绑定端口，开启监听 |
| 主循环 | `main()` | accept 连接，为每个客户端创建线程 |
| 请求解析与分发 | `accept_request()` | 读取 HTTP 请求行，决定走静态文件还是 CGI |
| 静态文件服务 | `serve_file()`, `headers()`, `cat()` | 发送响应头和文件内容 |
| CGI 执行 | `execute_cgi()` | fork 子进程，用管道通信，执行 CGI 脚本 |
| 辅助错误响应 | `not_found()`, `unimplemented()`, `bad_request()` 等 | 返回 404/501/400 等错误页面 |

下面我按照执行流程，详细说明每个模块做了什么。

### 2.1 服务器初始化：startup()

这个函数封装了 socket 初始化的标准流程：

- `socket(PF_INET, SOCK_STREAM, 0)` 创建 TCP socket
- `setsockopt(..., SO_REUSEADDR, ...)` 允许端口复用，避免服务器重启时报 Address already in use
- 构造 `sockaddr_in`，端口使用 `htons(port)` 转为网络字节序
- `bind()` 绑定
- 如果 port 参数传入 0，系统会自动分配一个空闲端口，然后用 `getsockname()` 拿到实际端口
- `listen()` 开启监听，队列长度 5

函数返回监听 socket 的文件描述符。

### 2.2 主循环：main()

main() 非常简短：

```c
server_sock = startup(&port);
while (1) {
    client_sock = accept(server_sock, ...);
    pthread_create(&newthread, NULL, (void *)accept_request, (void *)(intptr_t)client_sock);
}
```

核心逻辑：

循环调用 `accept()` 阻塞等待客户端连接，有连接到达时，`pthread_create()` 创建一个新线程，线程入口函数是 `accept_request()`，把客户端的 socket 作为参数传进去，主线程不等待子线程，立刻回到 `accept()` 继续接收新的连接

这种模型简单直接，每个请求单独一个线程，但对于高并发场景会产生大量线程，开销可能会比较大。

### 2.3 请求解析与分发：accept_request()

负责解析 HTTP 请求，决定后续的走向。

#### 第一步：读取请求行

```c
get_line(client, buf, sizeof(buf));
```

`get_line()` 是逐字符读取的，直到遇到 `\n` 才返回。它会把网络中的 `\r\n` 统一转成以 `\n` 结尾的字符串。

#### 第二步：解析 method 和 URL

从 `buf` 中提取第一个词作为 `method`，第二个词作为 `url`。tinyhttpd 只实现了 `GET` 和 `POST` 两种方法，如果遇到其他方法会返回 `501`。

#### 第三步：判断是否需要 CGI

判断规则：

- 如果是 `POST` 方法，`cgi = 1`
- 如果是 `GET` 方法且 URL 中包含 `?` 字符（说明有查询参数），`cgi = 1`

#### 第四步：构建服务器文件路径

```c
sprintf(path, "htdocs%s", url);
```

如果 `path` 以 `/` 结尾，就需要补上 `index.html`。然后才能调用 `stat()` 检查文件是否存在。如果文件是一个目录，同样也补上 `index.html`。最后，如果文件有执行权限（`S_IXUSR | S_IXGRP | S_IXOTH`），也把 `cgi` 设为 `1`。

#### 第五步：分发

- `cgi == 0` → 调用 `serve_file()`
- `cgi == 1` → 调用 `execute_cgi()`

### 2.4 静态文件服务

如果走静态文件路径，`accept_request()` 会调用 `serve_file()`：

```c
void serve_file(int client, const char *filename) {
    FILE *resource = fopen(filename, "r");
    headers(client, filename);
    cat(client, resource);
    fclose(resource);
}
```

`headers()` 负责发送 HTTP 响应头：

- `HTTP/1.0 200 OK`
- `Server: Tinyhttpd/0.1.0`
- `Content-Type: ...`（根据文件扩展名判断，如 `.html` → `text/html`，`.jpg` → `image/jpeg`）

一个空行表示头部结束。

`cat()` 则一行一行读取文件，通过 `send()` 发送到客户端 socket。

### 2.5 CGI 动态执行：execute_cgi()

这是 tinyhttpd 最复杂的部分，涉及进程间通信。我画了一张图来理解：

```text
graph TD
    A[父进程 accept_request] --> |fork| B[子进程]
    A -->|POST数据写入cgi_input管道| B
    B -->|执行 CGI 脚本，输出到stdout| C[cgi_output管道]
    C -->|父进程读取并发送| D[客户端]
```

具体步骤：

解析 `Content-Length`（只有 POST）：先循环调用 `get_line()` 读请求头，直到读到空行，期间解析 `Content-Length` 的值，知道后面需要读多少 POST 数据。

创建两个管道：
`cgi_output`：子进程把输出写进去，父进程读；`cgi_input`：父进程写入 POST 数据，子进程读

`fork` 子进程：

子进程：

- 重定向标准输出到 `cgi_output[1]`（`dup2`）
- 重定向标准输入到 `cgi_input[0]`
- 设置环境变量：`REQUEST_METHOD`，以及 `QUERY_STRING`- GET 或 `CONTENT_LENGTH`- POST
- 调用 `execl(path, path, NULL)`，将子进程替换为 CGI 脚本程序

父进程：

- 关闭不需要的管道端口（子进程会用的端口父进程关掉，反之亦然）
- 如果是 POST，读取 POST 数据并通过 `cgi_input[1]` 写入管道
- 从 `cgi_output[0]` 读取子进程的输出（即 CGI 脚本生成的页面内容），逐字节发送到客户端
- 调用 `wait(NULL)` 等待子进程结束
- 关闭管道，返回

这个过程中，环境变量的设置非常重要——CGI 规范要求服务器通过环境变量传递请求信息，脚本通过标准输入读 POST 数据，通过标准输出返回结果。


## 三、关键函数调用关系与数据流分析

### 3.1 调用关系图

我把整个调用链整理成了一张图（用文本形式表达）：

```text
main()
├── startup()           [创建监听 socket]
└── while(1)
    └── accept()        [获得 client_sock]
    └── pthread_create()
        └── accept_request(client)   [线程入口]
            ├── get_line()           [读请求行]
            ├── 解析 method / url
            ├── stat()               [获取文件信息]
            │
            ├── [静态文件分支] serve_file()
            │       ├── headers()
            │       └── cat()
            │
            └── [CGI分支] execute_cgi()
                    ├── read headers (找 Content-Length)
                    ├── pipe(cgi_output)
                    ├── pipe(cgi_input)
                    ├── fork()
                    │   ├── [子进程] dup2 / setenv / execl
                    │   └── [父进程] 读写管道 / wait
                    └── 返回
```

### 3.2 数据跟踪：以 GET /color.cgi?color=red 为例

**客户端 → 服务器：浏览器发送 `GET /color.cgi?color=red HTTP/1.1\r\n\r\n`**

服务器接收：accept() 返回 client_socket，新线程进入 accept_request()

解析：

1. get_line() 得到 "GET /color.cgi?color=red HTTP/1.1"

2. method = "GET", url = "/color.cgi?color=red"，因为 url 中有 ?，所以 cgi = 1

3. 路径构造：path = "htdocs/color.cgi"，stat() 成功且文件有执行权限

执行 CGI：

1. execute_cgi() 中 fork() 子进程

2. 子进程：QUERY_STRING = "color=red"

3. execl 执行 color.cgi，脚本解析参数，生成 HTML 页面，写入 stdout（即管道）

4. 父进程从管道读取输出，原样 send 给客户端，客户端收到响应：浏览器渲染页面，背景颜色变成红色

### 3.3 辅助函数列表

| 函数 | 作用 |
| --- | --- |
| `get_line()` | 从 socket 读取一行（支持 `\r\n` 和 `\n`） |
| `not_found()` | 返回 404 页面 |
| `unimplemented()` | 返回 501 错误（方法不支持） |
| `bad_request()` | 返回 400 错误（请求格式错误） |
| `cannot_execute()` | 返回 500 错误（CGI 执行失败） |
| `error_die()` | 打印错误并退出 |

## 四、思考题

在读完源码后，我给自己留了三个思考题，帮助理解设计上的取舍。

### 思考题 1：线程上有何缺陷以及如何改进？

tinyhttpd 为每个客户端请求创建一个新的线程。这个模型在并发量低时很直观，但存在几个问题：

1. 创建/销毁开销：频繁连接的场景下，线程的创建和销毁消耗 CPU 和内存。

2. 系统上限：每个线程占用栈空间（默认 8MB），1000 个线程就需要 8GB 内存，且操作系统可创建的线程数有限。

3. 吞吐量瓶颈：高并发时，大量线程竞争 CPU，上下文切换开销巨大。

改进方向：

1. 可以采用线程池，这可以避免频繁创建销毁线程。

2. 事件驱动 + 非阻塞 I/O：使用 epoll 或 kqueue，一个线程处理成千上万个连接（Nginx 采用的模式）。当连接可读或可写时再处理，不会阻塞在 I/O 上。

### 思考题 2：get_line() 逐字节读取的缺陷和如何优化？

`get_line()` 每次调用 `recv(sock, &c, 1, 0)` 只读取一个字节。这会导致：

大量系统调用：也就是说读 100 字节的请求行就要 100 次 recv，每次系统调用都有挺大的开销。

网络效率低：无法利用 TCP 的滑动窗口，实际上每次 recv(1) 不一定只从网络收 1 字节（可能缓冲区已经有多字节），但用户态一次只能拿到 1 字节，浪费了批量读取的机会。

改进方法：

1. 使用用户态缓冲区：recv 一次读取 4096 字节到缓冲区，然后从缓冲区中逐字节或逐行解析。

2. 使用 read 配合缓冲区，或者使用 fgets 配合 fdopen 将 socket 包装为 FILE*，但要注意非阻塞情况。

### 思考题 3：CGI 的性能如何以及方案有哪些替代？

当前的CGI 每次请求都要 fork 一个子进程，执行脚本，然后销毁。开销包括：

1. 进程创建的成本（fork + exec）

2. 地址空间复制（虽然写时复制会缓解，但仍有开销）

3. 环境变量传递、内存分配、库加载等

对于高频动态请求，这种方式无法支撑。

可替代的方案：

> 以下部分方案存在AIGC，不一定准确，理性采纳 

1. FastCGI：进程常驻，由 Web 服务器通过 socket 或 TCP 与 FastCGI 进程通信。一个进程可以处理多个请求，避免了反复 fork 的开销。PHP-FPM 就是 FastCGI 实现。

2. 嵌入解释器：如 Apache 的 mod_php，将 PHP 解释器嵌入到 Web 服务器进程中，直接执行脚本，没有进程间通信。

3. WSGI（Python）：定义了 Web 服务器与 Python Web 应用之间的接口，通常配合 uWSGI 或 Gunicorn 使用，也是进程/线程池模型。

4. 现代语言原生服务：Node.js、Go 等语言本身支持高并发，可以直接在应用内部实现 HTTP 服务，不再需要 CGI 这种外部接口。
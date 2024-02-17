<?php
$servername = "localhost";
$username = "software";
$password = "LkFhPnW5fLp4YCEA";
$dbname = "software";

// 设备
function GetOs() {
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $OS = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/win/i', $OS)) {
            $OS = 'Windows';
        } elseif (preg_match('/mac/i', $OS)) {
            $OS = 'MAC';
        } elseif (preg_match('/linux/i', $OS)) {
            $OS = 'Linux';
        } elseif (preg_match('/unix/i', $OS)) {
            $OS = 'Unix';
        } elseif (preg_match('/bsd/i', $OS)) {
            $OS = 'BSD';
        } else {
            $OS = 'Other';
        }
        return $OS;
    } else {
        return "获取访客操作系统信息失败！";
    }}

// 浏览器
function GetBrowser() {
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $br = 'MSIE';
        } elseif (preg_match('/Firefox/i', $br)) {
            $br = 'Firefox';
        } elseif (preg_match('/Chrome/i', $br)) {
            $br = 'Chrome';
        } elseif (preg_match('/Safari/i', $br)) {
            $br = 'Safari';
        } elseif (preg_match('/Opera/i', $br)) {
            $br = 'Opera';
        } else {
            $br = 'Other';
        }
        return $br;
    } else {
        return "获取浏览器信息失败！";
    }}

// IP地址
function getip() {
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP") , "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR") , "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR") , "unknown")) {
        $ip = getenv("REMOTE_ADDR");
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }
    return $ip;}

// 语言
function GetLang() {
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $lang = substr($lang, 0, 5);
        if (preg_match("/zh-cn/i", $lang)) {
            $lang = "简体中文";
        } elseif (preg_match("/zh/i", $lang)) {
            $lang = "繁体中文";
        } else {
            $lang = "English";
        }
        return $lang;
    } else {
        return "获取浏览器语言失败！";
    }}


try {
    // 尝试连接数据库
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // 设置 PDO 错误模式为异常
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 连接成功后，获取 app_id 最大值和对应的 is_downloadable
    $query = "SELECT app_id, is_downloadable, download_count FROM app WHERE app_id = (SELECT MAX(app_id) FROM app)";
    $result = $conn->query($query);
    
    if ($result) {
        // 获取结果集中的第一行数据
        $row = $result->fetch(PDO::FETCH_ASSOC);

        // 输出获取到的 app_id 和 is_downloadable
        $app_id = $row['app_id'];
        $is_downloadable = $row['is_downloadable'];
        $download_count = $row['download_count'];

        // 获取访问者的IP地址
        $visitor_ip = getip();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM ip_blacklist WHERE ip_address = ?");
        $stmt->bindParam(1, $visitor_ip);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        if ($count > 0) {
            http_response_code(403); // 返回403错误
            die("Forbidden: Your IP is blocked, please contact the webmaster.");
        }
    } else {
        echo "查询失败";
    }
} catch (PDOException $e) {
    // 连接数据库失败，输出错误信息
    echo "连接失败: " . $e->getMessage();
}

// 目标URL
$url = "http://opendata.baidu.com/api.php?query=".getip()."&co=&resource_id=6006&oe=utf8";

// 创建一个新cURL资源
$curl = curl_init();

// 设置cURL选项
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 将curl_exec()返回的结果存入变量，而不是直接输出

// 执行cURL请求并获取返回的数据
$response = curl_exec($curl);

// 检查是否有错误发生
if(curl_errno($curl)){
    echo 'cURL错误：' . curl_error($curl);
    exit;
}

// 关闭cURL资源
curl_close($curl);

// 解析JSON
$data = json_decode($response, true);

// 检查解析结果并提取location字段
if ($data !== null && isset($data['data'][0]['location'])) {
    // 提取location字段
    $location = $data['data'][0]['location'];
} else {
    // 解析失败或者location字段不存在
    die("error\n");
}

echo "<!--
网页获取信息（测试用的，不收集信息）：
IP：".getip()."
地址：$location
设备：".GetOs()."
浏览器（或内核）：".GetBrowser()."
客户端语言：".GetLang()."
黑名单IP：不是
-->\n";
?>
<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>一个软件</title>
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
        <link rel="stylesheet"
              href="./css/style.css">
        <link href="https://cdn.staticfile.org/twitter-bootstrap/5.1.1/css/bootstrap.min.css"
              rel="stylesheet">
        <script src="https://cdn.staticfile.org/twitter-bootstrap/5.1.1/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="window">
            <div class="title-bar">
                <span class="title">
                    <i class="fa fa-archive"></i> Winict Box
                    <span class="badge bg-success">1.0</span>
                </span>
                <div class="buttons">
                    <button class="minimize" onclick="window.location.href='./?page=about'">
                        <i class="fa fa-code"></i>
                    </button>
                    <button class="minimize" onclick="window.location.href='./?page=donation'">
                        <i class="fas fa-hand-holding-usd"></i>
                    </button>
                    <button class="minimize">
                    </button>
                    <button class="minimize">
                        <i class="fas fa-window-minimize"></i>
                    </button>
                    <button class="maximize">
                        <i class="fas fa-window-maximize"></i>
                    </button>
                    <button class="close">
                        <i class="fas fa-window-close"></i>
                    </button>
                </div>
            </div>
            <div class="window-text-h" style="overflow-y:auto">
                <?php if (!isset($_GET['page'])): ?>
                <div class="tab-content">
                    <h1>欢迎使用Winict Box</h1>
                    <p>Winict Box是一个强大的工具，提供各种功能，让您的工作更加高效。</p>

                    <div class="btn">
                        <img src="http://app.woskzm.cn/Images/main.png"
                             alt="Winict Box主程序"
                             width="70%">
                    </div>
                    <br>
                    <div class="btn-group">
                        <button type="button"
                                class="btn btn-primary"
                                onclick="window.location.href='/?page=download'">最新版本</button>
                        <button type="button"
                                class="btn btn-secondary"
                                onclick="window.location.href='myprotocol://key'">打开</button>
                        <button type="button"
                                class="btn btn-primary"
                                onclick="window.location.href='/?page=version'">版本列表</button>
                    </div>
                </div>
                <?php elseif ($_GET['page'] == 'download'): ?>
                <div class="tab-content">
                    <h1>下载</h1>
                    <p>在这里选择下载最新版。
                        <br>下载次数：
                        <?php echo $download_count; ?>
                        <br>请选择下载方式：</p>
                    <?php
                    // 实验性->下载限制解除
                    if ($_GET['experimental'] == 'true' && $_GET['rapidgator'] == 'false'){
                        echo "<button type=\"button\" class=\"btn btn-primary\" onclick=\"window.location.href='./download/?id=$app_id'\">本地下载</button>";
                    } else {
                        // 下载限制
                        if ($is_downloadable == '0') {
                            echo "<button type=\"button\" class=\"btn btn-primary\" disabled>禁止下载</button>";
                        } elseif ($is_downloadable == '1') {
                            echo "<button type=\"button\" class=\"btn btn-primary\" onclick=\"window.location.href='./download/?id=$app_id'\">本地下载</button>";
                        } else {
                            echo "<button type=\"button\" class=\"btn btn-primary\" disabled>数据异常</button>";
                        };
                    };
                    ?>
                </div>
            </div>
            <?php elseif ($_GET['page'] == 'version'): ?>
            <div class="tab-content">
                <h1>版本列表</h1>
                <p>在这里选择下载历史版本。</p>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>软件名</th>
                            <th>版本号</th>
                            <th>发布时间</th>
                            <th>下载权限</th>
                            <th>下载次数</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($_GET['test_app']) && $_GET['test_app'] === 'true'): ?>
                        <tr>
                            <td>测试应用</td>
                            <td>0.0.0.1</td>
                            <td>2024-02-05</td>
                            <td>是</td>
                            <td>测试应用，不计次</td>
                            <td>
                                <button type="button"
                                        class="btn btn-primary"
                                        onclick="window.location.href='./file/test.exe'">本地下载</button>
                            </td>
                        </tr>
                        <?php endif;?>
                        <?php
                        // 修改查询语句，选择所有列
                        $stmt = $conn->prepare("SELECT * FROM app");
                        // 重新执行查询
                        $stmt->execute();

                        // 输出表格
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['software_name'] . "</td>";
                            echo "<td>" . $row['software_version'] . "</td>";
                            echo "<td>" . $row['release_time'] . "</td>";
                            echo "<td>" . ($row['is_downloadable'] ? '是' : '否') . "</td>";
                            echo "<td>" . $row['download_count'] . "</td>";

                            // 实验性->下载限制解除
                            echo "<td>";
                            if ($_GET['experimental'] == 'true' && $_GET['rapidgator'] == 'false'){
                                echo "<button type=\"button\" class=\"btn btn-primary\" onclick=\"window.location.href='./download/?id=$app_id'\">本地下载</button>";
                            } else {
                                // 下载限制
                                if ($is_downloadable == '0') {
                                    echo "<button type=\"button\" class=\"btn btn-primary\" disabled>禁止下载</button>";
                                } elseif ($is_downloadable == '1') {
                                    echo "<button type=\"button\" class=\"btn btn-primary\" onclick=\"window.location.href='./download/?id=$app_id'\">本地下载</button>";
                                } else {
                                    echo "<button type=\"button\" class=\"btn btn-primary\" disabled>数据异常</button>";
                                };
                            };
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($_GET['page'] == 'about'): ?>
            <div class="tab-content">
                <h1>关于</h1>
                <p>软件信息</p>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>软件名</th>
                            <th>开发组</th>
                            <th>版权</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 执行 SQL 查询
                        $stmt = $conn->prepare("SELECT * FROM software");
                        // 执行查询
                        $stmt->execute();

                        // 输出表格
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['software_name'] . "</td>";
                            echo "<td>" . $row['development_team'] . "</td>";
                            echo "<td>" . $row['copyright'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <p>软件信息</p>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>人员名称</th>
                            <th>加入时间</th>
                            <th>QQ</th>
                            <th>邮箱</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 执行 SQL 查询
                        $stmt = $conn->prepare("SELECT * FROM gratitudelist");
                        // 执行查询
                        $stmt->execute();

                        // 输出表格
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . $row['PersonName'] . "</td>";
                            echo "<td>" . $row['JoinTime'] . "</td>";
                            echo "<td>" . $row['QQ'] . "</td>";
                            echo "<td>" . $row['Email'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <p>页面信息</p>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>页面名称</th>
                            <th>作者</th>
                            <th>QQ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 执行 SQL 查询
                        $stmt = $conn->prepare("SELECT * FROM about");
                        // 执行查询
                        $stmt->execute();

                        // 输出表格
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $tx = "<img src=\"https://q.qlogo.cn/g?b=qq&nk=" . $row['qq'] . "&s=100\" alt=\"Circular Image\" width=\"5%\"> ";
                            echo "<tr>";
                            echo "<td>" . $row['page_name'] . "</td>";
                            echo "<td>" . $tx . $row['author'] . "</td>";
                            echo "<td>" . $row['qq'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($_GET['page'] == 'donation'): ?>
                <div class="tab-content">
                    <h1>捐赠</h1>
                    <p>我们暂时不接受捐助，但是你可以给我们点一个赞。</p>
                    <button type="button" class="btn" onclick="window.location.href='./likes/'">👍</button>
<?php
// 修改查询语句，选择所有列
$stmt = $conn->prepare("SELECT * FROM likes");

// 重新执行查询
$stmt->execute();

// 输出表格
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<p>已获得 " . $row['likes_count'] . " 个赞</p>";
}
?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </body>
</html>
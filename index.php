<?php
$servername = "localhost";
$username = "software";
$password = "xxxxxxx";
$dbname = "software";



try {
    // Try to connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to Exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // After the connection is successful, obtain the maximum value of app_id and the corresponding is_downloadable
    $query = "SELECT app_id, is_downloadable, download_count FROM app WHERE app_id = (SELECT MAX(app_id) FROM app)";
    $result = $conn->query($query);
    
    if ($result) {
        // Get the first row of data in the result set
        $row = $result->fetch(PDO::FETCH_ASSOC);

        // Outputs the app_id and is_downloadable obtained
        $app_id = $row['app_id'];
        $is_downloadable = $row['is_downloadable'];
        $download_count = $row['download_count'];

        // Get the IP address of the visitor
        $visitor_ip = getip();

        $stmt = $conn->prepare("SELECT COUNT(*) FROM ip_blacklist WHERE ip_address = ?");
        $stmt->bindParam(1, $visitor_ip);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        if ($count > 0) {
            http_response_code(403); // A 403 error is returned
            die("Forbidden: Your IP is blocked, please contact the webmaster.");
        }
    } else {
        echo "The query failed";
    }
} catch (PDOException $e) {
    // Failed to connect to the database and an error message is output
    echo "Connection Failure: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <title>A piece of software</title>
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
                    <i class="fa fa-archive"></i> xxxxxxx
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
                    <h1>Welcome to xxx</h1>
                    <p>XXX is a powerful tool that offers a variety of features to make your work more efficient.</p>

                    <div class="btn">
                        <img src="http://app.woskzm.cn/Images/main.png"
                             alt="xxx main program"
                             width="70%">
                    </div>
                    <br>
                    <div class="btn-group">
                        <button type="button"
                                class="btn btn-primary"
                                onclick="window.location.href='/?page=download'">The latest version</button>
                        <button type="button"
                                class="btn btn-secondary"
                                onclick="window.location.href='myprotocol://key'">Open it</button>
                        <button type="button"
                                class="btn btn-primary"
                                onclick="window.location.href='/?page=version'">List of versions</button>
                    </div>
                </div>
                <?php elseif ($_GET['page'] == 'download'): ?>
                <div class="tab-content">
                    <h1>Download</h1>
                    <p>Choose here to download the latest version.
                        <br>Downloads:
                        <?php echo $download_count; ?>
                        <br>Please select a download method:</p>
                    <?php
                    // Experimental - > download restrictions lifted
                    if ($_GET['experimental'] == 'true' && $_GET['rapidgator'] == 'false'){
                        echo "<button type=\"button\" class=\"btn btn-primary\" onclick=\"window.location.href='./download/?id=$app_id'\">本地下载</button>";
                    } else {
                        // Download Limitations
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
                        // Execute SQL queries
                        $stmt = $conn->prepare("SELECT * FROM gratitudelist");
                        // Execute the query
                        $stmt->execute();

                        // Output table
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
                <p>Page Information</p>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>The name of the page</th>
                            <th>author</th>
                            <th>QQ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Execute SQL queries
                        $stmt = $conn->prepare("SELECT * FROM about");
                        // Execute the query
                        $stmt->execute();

                        // Output table
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
                    <h1>donation</h1>
                    <p>We don't accept donations at the moment, but you can give us a thumbs up.</p>
                    <button type="button" class="btn" onclick="window.location.href='./likes/'">👍</button>
<?php
// Modify the query statement and select all columns
$stmt = $conn->prepare("SELECT * FROM likes");

// Re-execute the query
$stmt->execute();

// Output table
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<p>" . $row['likes_count'] . " likes</p>";
}
?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </body>
</html>

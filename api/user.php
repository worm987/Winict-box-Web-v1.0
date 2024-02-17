<?php

$host = 'localhost';
$dbname = 'software';
$user = 'software';
$password = 'xxxxx';

// 创建PDO实例连接数据库
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // 设置错误模式为异常
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 检查是否有POST请求和token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'];

    // 准备SQL语句查询token
    $stmt = $pdo->prepare("SELECT user_id, token FROM tokens WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);

    // 判断是否找到token
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'];

        // 使用找到的user_id和token获取cmd表中的信息
        $stmt = $pdo->prepare("SELECT * FROM cmd WHERE user_id = :user_id AND token = :token");
        $stmt->execute([':user_id' => $user_id, ':token' => $token]);

        if ($stmt->rowCount() > 0) {
            // 获取数据，拼接JSON消息
            $cmds_json = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cmds = json_decode($cmds_json, true);
            // 判断
            if ($cmds['command'] == "None") {
                // 无cmd信息
                $response = [
                    'error' => false,
                    'cmd' => false,
                    'msg' => '',
                    'cmds' => '',
                    'token' => $token
                ];
            } else {
                $response = [
                    'error' => false,
                    'cmd' => true,
                    'msg' => '',
                    'cmds' => $cmds_json, // 从数据库获取的cmd表信息
                    'token' => $token
                ];

                // 更新cmd表中的command字段为空
                $updateStmt = $pdo->prepare("UPDATE cmd SET command = '' WHERE user_id = :user_id AND token = :token");
                $updateStmt->execute([':user_id' => $user_id, ':token' => $token]);
            }

            echo json_encode($response);
        } else {
            // 没有找到cmd表信息的情况
            $response = [
                'error' => true,
                'cmd' => false,
                'msg' => '数据库异常！',
                'token' => $token
            ];

            echo json_encode($response);
        }
    } else {
        // 没有找到token的情况
        $response = [
            'error' => true,
            'cmd' => false,
            'msg' => '令牌无效或失效！',
            'token' => $token
        ];

        echo json_encode($response);
    }
} else {
    // 没有POST请求或没有提供token的情况
    $response = [
        'error' => true,
        'cmd' => false,
        'msg' => '缺少令牌',
        'token' => ''
    ];

    echo json_encode($response);
}
?>

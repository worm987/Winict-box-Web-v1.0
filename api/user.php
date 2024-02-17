<?php

$host = 'localhost';
$dbname = 'software';
$user = 'software';
$password = 'xxxxx';

// Create a PDO instance to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Set the error mode to Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failure: " . $e->getMessage());
}

// Check whether there is a POST request and a token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'];

    // Prepare an SQL statement to query tokens
    $stmt = $pdo->prepare("SELECT user_id, token FROM tokens WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);

    // Determine whether the token is found
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $row['user_id'];

        // Use the found user_id and tokens to get the information in the cmd table
        $stmt = $pdo->prepare("SELECT * FROM cmd WHERE user_id = :user_id AND token = :token");
        $stmt->execute([':user_id' => $user_id, ':token' => $token]);

        if ($stmt->rowCount() > 0) {
            // Obtain data and splice JSON messages
            $cmds_json = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $cmds = json_decode($cmds_json, true);
            // judgment
            if ($cmds['command'] == "None") {
                // No CMD information
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
                    'cmds' => $cmds_json, // The CMD table information obtained from the database
                    'token' => $token
                ];

                // Update that the command field in the cmd table is empty
                $updateStmt = $pdo->prepare("UPDATE cmd SET command = '' WHERE user_id = :user_id AND token = :token");
                $updateStmt->execute([':user_id' => $user_id, ':token' => $token]);
            }

            echo json_encode($response);
        } else {
            // No information was found for the cmd table
            $response = [
                'error' => true,
                'cmd' => false,
                'msg' => 'Database exception!',
                'token' => $token
            ];

            echo json_encode($response);
        }
    } else {
        // No token found
        $response = [
            'error' => true,
            'cmd' => false,
            'msg' => 'The token is invalid or invalid!',
            'token' => $token
        ];

        echo json_encode($response);
    }
} else {
    // When there is no POST request or no token provided
    $response = [
        'error' => true,
        'cmd' => false,
        'msg' => 'Missing tokens',
        'token' => ''
    ];

    echo json_encode($response);
}
?>

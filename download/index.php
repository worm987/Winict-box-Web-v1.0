<?php
$appId = 1;
$downloadCount = 1;

$id = $_GET['id'];

$servername = "localhost";
$username = "software";
$password = "xxxxxxx";
$dbname = "software";

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


try {
    // Get the IP address of the visitor
    $visitor_ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM ip_blacklist WHERE ip_address = ?");
    $stmt->bindParam(1, $visitor_ip);
    $stmt->execute();

    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(403); // A 403 error is returned
        die("Forbidden: Your IP is blocked, please contact the webmaster.");
    };

    // The number of update downloads
    $stmt = $conn->prepare("UPDATE app SET download_count = download_count + :downloadCount");
    $stmt->bindParam(':downloadCount', $downloadCount, PDO::PARAM_INT);
    $stmt->execute();

    // Get the download link
    $stmt = $conn->prepare("SELECT download_url FROM app WHERE app_id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $download_url = $stmt->fetchColumn();

    // If a download link is found, you are redirected to it
    if ($download_url) {
        header("Location: $download_url");
        exit();
    } else {
        echo "Download URL not found for ID: $id";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

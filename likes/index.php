<?php
// Execute the following code after downloading the software to update the number of downloads
$appId = 1; // Replace with the ID of the corresponding software
$downloadCount = 1; // The number of times the user downloads can be changed


$servername = "localhost";
$username = "software";
$password = "LkFhPnW5fLp4YCEA";
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
    // Update the number of likes
    $stmt = $conn->prepare("UPDATE likes SET likes_count = likes_count + :downloadCount");
    $stmt->bindParam(':downloadCount', $downloadCount, PDO::PARAM_INT);
    $stmt->execute();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<script>
window.location.href='/?page=donation'
</script>

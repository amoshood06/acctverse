<?php
// Database connection settings
$host = "localhost";
$dbname = "lauowlwj_acctverse";
$username = "lauowlwj_acctverse";
$password = "acctverse123";
// $host = "localhost";
// $dbname = "9tech";
// $username = "root";
// $password = "";

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set PDO error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Stop the script if connection fails
    die("Database connection failed: " . $e->getMessage());
}

return $pdo;
?>

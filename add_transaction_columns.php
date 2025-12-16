<?php
$pdo = require_once "db/db.php";

try {
    $sql = "ALTER TABLE transactions 
            ADD COLUMN transaction_reference VARCHAR(255) NULL AFTER type,
            ADD COLUMN payment_gateway VARCHAR(50) NULL AFTER transaction_reference;";
    $pdo->exec($sql);
    echo "Columns 'transaction_reference' and 'payment_gateway' added to 'transactions' table successfully.";
} catch (PDOException $e) {
    echo "Error altering table: " . $e->getMessage();
}
?>
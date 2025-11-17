<?php
/**
 * Database connection using PDO.
 *
 * This file creates a PDO instance in $pdo and also provides $dbh and $conn
 * aliases for compatibility with older code. Configure database credentials
 * via environment variables or by editing the defaults below.
 *
 * Recommended environment variables:
 *  - ACCT_DB_HOST (default: 127.0.0.1)
 *  - ACCT_DB_PORT (default: 3306)
 *  - ACCT_DB_NAME (default: acctverse)
 *  - ACCT_DB_USER (default: root)
 *  - ACCT_DB_PASS (default: empty)
 */

// Prevent accidental direct output
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}

// Defaults - edit if you prefer hardcoded values
$dbHost = getenv('ACCT_DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('ACCT_DB_PORT') ?: '3306';
$dbName = getenv('ACCT_DB_NAME') ?: 'acctverse';
$dbUser = getenv('ACCT_DB_USER') ?: 'root';
$dbPass = getenv('ACCT_DB_PASS') ?: '';

// Allow overriding via constants if defined elsewhere
if (defined('ACCT_DB_HOST')) $dbHost = constant('ACCT_DB_HOST');
if (defined('ACCT_DB_PORT')) $dbPort = constant('ACCT_DB_PORT');
if (defined('ACCT_DB_NAME')) $dbName = constant('ACCT_DB_NAME');
if (defined('ACCT_DB_USER')) $dbUser = constant('ACCT_DB_USER');
if (defined('ACCT_DB_PASS')) $dbPass = constant('ACCT_DB_PASS');

$pdo = null;
try {
	$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];
	$pdo = new PDO($dsn, $dbUser, $dbPass, $options);
	// backward-compatible aliases
	$dbh = $pdo;
	$conn = $pdo;
} catch (PDOException $e) {
	// In development you may want to see the error. In production, log it and show a generic message.
	error_log('Database connection error: ' . $e->getMessage());
	// If headers are not sent, you might want to stop execution; otherwise fail gracefully.
	if (!headers_sent()) {
		// Minimal HTML friendly message for local dev.
		http_response_code(500);
		echo '<h1>Database connection error</h1>';
		if (getenv('APP_ENV') === 'development') {
			echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
		}
		exit;
	}
}

// Optional helper to retrieve the PDO instance
if (!function_exists('get_pdo')) {
	function get_pdo() {
		global $pdo;
		return $pdo;
	}
}

?>

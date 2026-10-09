<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "jasaan_tourism";

// Load the rate limiter before database work so repeated requests are stopped early.
require_once __DIR__ . "/rate_limit.php";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

require_once __DIR__ . "/schema.php";

// Schema upgrades use ALTER TABLE statements, so serialize them across simultaneous web requests.
$schemaLockResult = $conn->query("SELECT GET_LOCK('jasaan_tourism_schema', 15) AS schema_lock");
$schemaLock = $schemaLockResult ? (int) ($schemaLockResult->fetch_assoc()['schema_lock'] ?? 0) : 0;
$schemaLockResult?->free();

if ($schemaLock !== 1) {
    throw new RuntimeException('The database schema is busy. Please retry the request.');
}

try {
    ensureJasaanTourismSchema($conn);
} finally {
    $conn->query("SELECT RELEASE_LOCK('jasaan_tourism_schema')");
}

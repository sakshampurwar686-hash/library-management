<?php
// Database connection + small helpers (all queries use prepared statements)
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli(
    '127.0.0.1',
    'library_user',
    getenv('DB_PASSWORD'),
    'library_db',
    3306
);// XAMPP defaults
$conn->set_charset('utf8mb4');

// Run a prepared statement: db_run($sql, 'si', [$a, $b]) -> mysqli_stmt
function db_run($sql, $types = '', $params = []) {
    global $conn;
    $st = $conn->prepare($sql);
    if ($types) $st->bind_param($types, ...$params);
    $st->execute();
    return $st;
}
function db_all($sql, $types = '', $params = []) { return db_run($sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC); }
function db_one($sql, $types = '', $params = []) { return db_run($sql, $types, $params)->get_result()->fetch_assoc(); }
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }          // escape output
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }                  // one-time message
function redirect($url) { header("Location: $url"); exit; }

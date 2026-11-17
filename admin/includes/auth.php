<?php
// Protects admin pages: redirect to login if not authenticated
require_once __DIR__ . '/db.php';
if (empty($_SESSION['admin_id'])) redirect('login.php');

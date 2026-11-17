<?php
// Destroy session and go back to login
require_once __DIR__ . '/includes/db.php';
session_destroy();
redirect('login.php');

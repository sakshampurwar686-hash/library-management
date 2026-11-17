<?php
// Destroy session and go back to login
require_once '../includes/db.php';
session_destroy();
redirect('login.php');

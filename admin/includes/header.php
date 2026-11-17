<?php
// Shared admin layout: top navbar + sidebar. Set $title before including.
require_once __DIR__ . '/auth.php';
$page = basename($_SERVER['PHP_SELF']);
$menu = [
  'dashboard.php'       => ['speedometer2', 'Dashboard'],
  'manage_books.php'    => ['book', 'Books'],
  'add_book.php'        => ['plus-circle', 'Add Book'],
  'manage_students.php' => ['people', 'Students'],
  'add_student.php'     => ['person-plus', 'Add Student'],
  'issue_book.php'      => ['box-arrow-up-right', 'Issue Book'],
  'return_book.php'     => ['arrow-return-left', 'Return Book'],
  'reports.php'         => ['bar-chart-line', 'Reports'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Admin') ?> | Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<!-- Top navbar -->
<nav class="navbar navbar-dark bg-dark sticky-top px-3">
  <button class="btn btn-outline-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar"><i class="bi bi-list"></i></button>
  <a class="navbar-brand ms-2 me-auto" href="dashboard.php"><i class="bi bi-journal-bookmark-fill"></i> Library Admin</a>
  <span class="text-light me-3 d-none d-sm-inline"><i class="bi bi-person-circle"></i> <?= e($_SESSION['admin_name']) ?></span>
  <a href="logout.php" class="btn btn-sm btn-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
</nav>
<div class="d-flex">
  <!-- Sidebar (offcanvas on mobile) -->
  <div class="offcanvas-lg offcanvas-start bg-white border-end sidebar" id="sidebar">
    <div class="offcanvas-header"><h5>Menu</h5><button class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button></div>
    <div class="offcanvas-body p-0"><div class="list-group list-group-flush w-100">
      <?php foreach ($menu as $file => [$icon, $label]): ?>
        <a href="<?= $file ?>" class="list-group-item list-group-item-action <?= $page === $file ? 'active' : '' ?>"><i class="bi bi-<?= $icon ?> me-2"></i><?= $label ?></a>
      <?php endforeach; ?>
    </div></div>
  </div>
  <!-- Content area -->
  <main class="flex-grow-1 p-3 p-md-4 content">
    <h3 class="mb-3"><?= e($title ?? '') ?></h3>
    <?php if (!empty($_SESSION['flash'])): [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="alert alert-<?= $t ?> alert-dismissible fade show"><?= e($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

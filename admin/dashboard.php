<?php
$title = 'Dashboard';
require_once '../includes/header.php';
// Statistics
$s = [
  ['Total Books',    db_one('SELECT COALESCE(SUM(quantity),0) c FROM books')['c'],            'book',            'primary'],
  ['Total Students', db_one('SELECT COUNT(*) c FROM students')['c'],                          'people',          'success'],
  ['Issued Books',   db_one("SELECT COUNT(*) c FROM issued_books WHERE status='issued'")['c'],'box-arrow-up-right','warning'],
  ['Returned Books', db_one("SELECT COUNT(*) c FROM issued_books WHERE status='returned'")['c'],'arrow-return-left','info'],
  ['Available Books',db_one('SELECT COALESCE(SUM(available_quantity),0) c FROM books')['c'], 'check2-circle',   'secondary'],
];
?>
<div class="row g-3">
<?php foreach ($s as [$label, $n, $icon, $color]): ?>
  <div class="col-12 col-sm-6 col-xl-4">
    <div class="card stat-card text-bg-<?= $color ?>"><div class="card-body d-flex justify-content-between align-items-center">
      <div><div class="fs-1 fw-bold"><?= $n ?></div><div><?= $label ?></div></div><i class="bi bi-<?= $icon ?>"></i>
    </div></div>
  </div>
<?php endforeach; ?>
</div>
<?php require_once '../includes/footer.php'; ?>

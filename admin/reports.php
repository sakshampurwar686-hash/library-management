<?php
$title = 'Reports';
require_once '../includes/header.php';
$base = 'SELECT i.*, b.title, s.name FROM issued_books i JOIN books b ON b.id=i.book_id JOIN students s ON s.id=i.student_id';
$issued   = db_all("$base WHERE i.status='issued' ORDER BY i.issue_date DESC");
$returned = db_all("$base WHERE i.status='returned' ORDER BY i.returned_on DESC");
$overdue  = db_all("$base WHERE i.status='issued' AND i.return_date < CURDATE() ORDER BY i.return_date");

// Render a report table; $ret = show returned-on column
function report_table($rows, $ret = false) { ?>
  <div class="table-responsive"><table class="table table-striped align-middle mb-0">
    <thead class="table-light"><tr><th>#</th><th>Book</th><th>Student</th><th>Issued</th><th>Due</th><?= $ret ? '<th>Returned On</th>' : '<th>Days Late</th>' ?></tr></thead><tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td><?= $r['id'] ?></td><td><?= e($r['title']) ?></td><td><?= e($r['name']) ?></td><td><?= $r['issue_date'] ?></td><td><?= $r['return_date'] ?></td>
        <td><?= $ret ? e($r['returned_on']) : max(0, (int)((time() - strtotime($r['return_date'])) / 86400)) ?></td></tr>
    <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">No records.</td></tr><?php endif; ?>
  </tbody></table></div>
<?php } ?>
<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t1">Issued (<?= count($issued) ?>)</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t2">Returned (<?= count($returned) ?>)</button></li>
  <li class="nav-item"><button class="nav-link text-danger" data-bs-toggle="tab" data-bs-target="#t3">Overdue (<?= count($overdue) ?>)</button></li>
</ul>
<div class="card"><div class="tab-content">
  <div class="tab-pane fade show active" id="t1"><?php report_table($issued); ?></div>
  <div class="tab-pane fade" id="t2"><?php report_table($returned, true); ?></div>
  <div class="tab-pane fade" id="t3"><?php report_table($overdue); ?></div>
</div></div>
<?php require_once '../includes/footer.php'; ?>

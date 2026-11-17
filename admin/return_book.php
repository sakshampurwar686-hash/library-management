<?php
$title = 'Return Book';
require_once '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['return'];
    $rec = db_one("SELECT book_id FROM issued_books WHERE id=? AND status='issued'", 'i', [$id]);
    if ($rec) {
        $conn->begin_transaction();
        db_run("UPDATE issued_books SET status='returned', returned_on=CURDATE() WHERE id=?", 'i', [$id]);
        db_run('UPDATE books SET available_quantity = available_quantity + 1 WHERE id=?', 'i', [$rec['book_id']]);
        $conn->commit();
        flash('success', 'Book marked as returned.');
    } else flash('danger', 'Record not found or already returned.');
    redirect('return_book.php');
}
$rows = db_all("SELECT i.*, b.title, s.name FROM issued_books i JOIN books b ON b.id=i.book_id JOIN students s ON s.id=i.student_id WHERE i.status='issued' ORDER BY i.return_date");
?>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><th>#</th><th>Book</th><th>Student</th><th>Issued</th><th>Due</th><th>Action</th></tr></thead><tbody>
  <?php foreach ($rows as $r): $late = $r['return_date'] < date('Y-m-d'); ?>
    <tr><td><?= $r['id'] ?></td><td><?= e($r['title']) ?></td><td><?= e($r['name']) ?></td><td><?= $r['issue_date'] ?></td>
      <td><?= $r['return_date'] ?> <?= $late ? '<span class="badge text-bg-danger">Overdue</span>' : '' ?></td>
      <td><form method="post" onsubmit="return confirm('Mark as returned?')"><button name="return" value="<?= $r['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-check2-circle"></i> Return</button></form></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">No books currently issued.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once '../includes/footer.php'; ?>

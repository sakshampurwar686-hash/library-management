<?php
$title = 'Issue Book';
require_once '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid = (int)$_POST['book_id']; $sid = (int)$_POST['student_id']; $due = $_POST['return_date'];
    if ($due < date('Y-m-d')) {
        echo '<div class="alert alert-danger">Return date cannot be in the past.</div>';
    } else {
        $conn->begin_transaction();   // keep stock and issue record consistent
        // Decrease stock only if a copy is available
        $st = db_run('UPDATE books SET available_quantity = available_quantity - 1 WHERE id=? AND available_quantity > 0', 'i', [$bid]);
        if ($st->affected_rows === 1) {
            db_run("INSERT INTO issued_books (book_id,student_id,issue_date,return_date,status) VALUES (?,?,CURDATE(),?,'issued')", 'iis', [$bid, $sid, $due]);
            $conn->commit(); flash('success', 'Book issued successfully.'); redirect('issue_book.php');
        }
        $conn->rollback();
        echo '<div class="alert alert-danger">Book is not available.</div>';
    }
}
$students = db_all('SELECT id,name,email FROM students ORDER BY name');
$books = db_all('SELECT id,title,author,available_quantity FROM books WHERE available_quantity>0 ORDER BY title');
?>
<div class="card"><div class="card-body"><form method="post" class="row g-3">
  <div class="col-md-4"><label class="form-label">Student</label><select name="student_id" class="form-select" required>
    <option value="">-- Select student --</option><?php foreach ($students as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['email']) ?>)</option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Book</label><select name="book_id" class="form-select" required>
    <option value="">-- Select book --</option><?php foreach ($books as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['title']) ?> - <?= e($b['author']) ?> [<?= $b['available_quantity'] ?> left]</option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Return Date</label><input type="date" name="return_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required></div>
  <div class="col-12"><button class="btn btn-primary"><i class="bi bi-box-arrow-up-right"></i> Issue Book</button></div>
</form></div></div>
<?php require_once '../includes/footer.php'; ?>

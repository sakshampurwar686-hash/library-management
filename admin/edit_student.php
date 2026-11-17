<?php
$title = 'Edit Student';
require_once '../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$st = db_one('SELECT * FROM students WHERE id=?', 'i', [$id]);
if (!$st) { flash('danger', 'Student not found.'); redirect('manage_students.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        db_run('UPDATE students SET name=?,email=?,phone=? WHERE id=?', 'sssi', [trim($_POST['name']), trim($_POST['email']), trim($_POST['phone']), $id]);
        flash('success', 'Student updated.'); redirect('manage_students.php');
    } catch (mysqli_sql_exception $ex) { echo '<div class="alert alert-danger">Email already exists.</div>'; }
}
?>
<div class="card"><div class="card-body"><form method="post" class="row g-3">
  <div class="col-md-4"><label class="form-label">Name</label><input name="name" value="<?= e($st['name']) ?>" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" value="<?= e($st['email']) ?>" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" value="<?= e($st['phone']) ?>" class="form-control"></div>
  <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save"></i> Update</button> <a href="manage_students.php" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once '../includes/footer.php'; ?>

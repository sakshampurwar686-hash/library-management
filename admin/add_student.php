<?php
$title = 'Add Student';
require_once '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        db_run('INSERT INTO students (name,email,phone) VALUES (?,?,?)', 'sss', [trim($_POST['name']), trim($_POST['email']), trim($_POST['phone'])]);
        flash('success', 'Student added.'); redirect('manage_students.php');
    } catch (mysqli_sql_exception $ex) { echo '<div class="alert alert-danger">Email already exists.</div>'; }
}
?>
<div class="card"><div class="card-body"><form method="post" class="row g-3">
  <div class="col-md-4"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control"></div>
  <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save"></i> Save Student</button> <a href="manage_students.php" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once '../includes/footer.php'; ?>

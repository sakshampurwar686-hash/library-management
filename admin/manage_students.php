<?php
$title = 'Manage Students';
require_once '../includes/header.php';
// Delete student (blocked if issue history exists)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    if (db_one('SELECT COUNT(*) c FROM issued_books WHERE student_id=?', 'i', [$id])['c'] > 0)
        echo '<div class="alert alert-danger">Cannot delete: student has issue records.</div>';
    else { db_run('DELETE FROM students WHERE id=?', 'i', [$id]); flash('success', 'Student deleted.'); redirect('manage_students.php'); }
}
$students = db_all('SELECT * FROM students ORDER BY id DESC');
?>
<div class="mb-3 text-end"><a href="add_student.php" class="btn btn-success"><i class="bi bi-person-plus"></i> Add Student</a></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($students as $s): ?>
    <tr><td><?= $s['id'] ?></td><td><?= e($s['name']) ?></td><td><?= e($s['email']) ?></td><td><?= e($s['phone']) ?></td><td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
      <td class="text-nowrap"><a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this student?')"><button name="delete" value="<?= $s['id'] ?>" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form></td></tr>
  <?php endforeach; if (!$students): ?><tr><td colspan="6" class="text-center text-muted py-4">No students yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php require_once '../includes/footer.php'; ?>

<?php
$title = 'Manage Books';
require_once '../includes/header.php';
// Delete book (blocked if it has issue history)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int)$_POST['delete'];
    if (db_one('SELECT COUNT(*) c FROM issued_books WHERE book_id=?', 'i', [$id])['c'] > 0)
        echo '<div class="alert alert-danger">Cannot delete: this book has issue records.</div>';
    else { db_run('DELETE FROM books WHERE id=?', 'i', [$id]); flash('success', 'Book deleted.'); redirect('manage_books.php'); }
}
// Search by title / author / category / ISBN
$q = trim($_GET['q'] ?? ''); $like = "%$q%";
$books = db_all('SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ? ORDER BY id DESC', 'ssss', [$like,$like,$like,$like]);
?>
<form class="row g-2 mb-3" method="get">
  <div class="col-12 col-md-6"><input name="q" value="<?= e($q) ?>" class="form-control" placeholder="Search title, author, category, ISBN"></div>
  <div class="col-auto"><button class="btn btn-primary"><i class="bi bi-search"></i> Search</button> <a href="manage_books.php" class="btn btn-outline-secondary">Reset</a></div>
  <div class="col-auto ms-md-auto"><a href="add_book.php" class="btn btn-success"><i class="bi bi-plus-lg"></i> Add Book</a></div>
</form>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
  <thead class="table-light"><tr><th>#</th><th>Title</th><th>Author</th><th>Category</th><th>ISBN</th><th>Qty</th><th>Available</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($books as $b): ?>
    <tr><td><?= $b['id'] ?></td><td><?= e($b['title']) ?></td><td><?= e($b['author']) ?></td><td><?= e($b['category']) ?></td><td><?= e($b['isbn']) ?></td>
      <td><?= $b['quantity'] ?></td>
      <td><span class="badge text-bg-<?= $b['available_quantity'] > 0 ? 'success' : 'danger' ?>"><?= $b['available_quantity'] ?></span></td>
      <td class="text-nowrap">
        <a href="edit_book.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
        <form method="post" class="d-inline" onsubmit="return confirm('Delete this book?')"><button name="delete" value="<?= $b['id'] ?>" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button></form>
      </td></tr>
  <?php endforeach; if (!$books): ?><tr><td colspan="8" class="text-center text-muted py-4">No books found.</td></tr><?php endif; ?>
  </tbody></table></div></div>
<?php require_once '../includes/footer.php'; ?>

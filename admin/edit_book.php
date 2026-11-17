<?php
$title = 'Edit Book';
require_once '../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$book = db_one('SELECT * FROM books WHERE id=?', 'i', [$id]);
if (!$book) { flash('danger', 'Book not found.'); redirect('manage_books.php'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = max(0, (int)$_POST['quantity']);
    $issued = $book['quantity'] - $book['available_quantity'];   // copies currently out
    if ($qty < $issued) {
        echo "<div class='alert alert-danger'>Quantity cannot be less than currently issued copies ($issued).</div>";
    } else {
        try {
            // available_quantity = new total - issued copies
            db_run('UPDATE books SET title=?,author=?,category=?,isbn=?,quantity=?,available_quantity=? WHERE id=?',
                   'ssssiii', [trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['isbn']), $qty, $qty - $issued, $id]);
            flash('success', 'Book updated.'); redirect('manage_books.php');
        } catch (mysqli_sql_exception $ex) { echo '<div class="alert alert-danger">ISBN already exists.</div>'; }
    }
}
?>
<div class="card"><div class="card-body"><form method="post" class="row g-3">
  <div class="col-md-6"><label class="form-label">Title</label><input name="title" value="<?= e($book['title']) ?>" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Author</label><input name="author" value="<?= e($book['author']) ?>" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Category</label><input name="category" value="<?= e($book['category']) ?>" class="form-control" required></div>
  <div class="col-md-5"><label class="form-label">ISBN</label><input name="isbn" value="<?= e($book['isbn']) ?>" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Total Quantity</label><input type="number" name="quantity" min="0" value="<?= $book['quantity'] ?>" class="form-control" required></div>
  <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save"></i> Update</button> <a href="manage_books.php" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once '../includes/footer.php'; ?>

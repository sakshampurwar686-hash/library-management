<?php
$title = 'Add Book';
require_once '../includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = max(1, (int)$_POST['quantity']);
    try {
        db_run('INSERT INTO books (title,author,category,isbn,quantity,available_quantity) VALUES (?,?,?,?,?,?)',
               'ssssii', [trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['isbn']), $qty, $qty]);
        flash('success', 'Book added successfully.');
        redirect('manage_books.php');
    } catch (mysqli_sql_exception $ex) {
        echo '<div class="alert alert-danger">Error: ISBN already exists or data is invalid.</div>';
    }
}
?>
<div class="card"><div class="card-body"><form method="post" class="row g-3">
  <div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Author</label><input name="author" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Category</label><input name="category" class="form-control" required></div>
  <div class="col-md-5"><label class="form-label">ISBN</label><input name="isbn" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Quantity</label><input type="number" name="quantity" min="1" value="1" class="form-control" required></div>
  <div class="col-12"><button class="btn btn-primary"><i class="bi bi-save"></i> Save Book</button> <a href="manage_books.php" class="btn btn-secondary">Cancel</a></div>
</form></div></div>
<?php require_once '../includes/footer.php'; ?>

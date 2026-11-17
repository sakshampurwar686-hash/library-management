<?php
// Admin login page
require_once '../includes/db.php';
if (!empty($_SESSION['admin_id'])) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = db_one('SELECT * FROM admins WHERE email=?', 's', [trim($_POST['email'])]);
    if ($admin && password_verify($_POST['password'], $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        redirect('dashboard.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en"><head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login | Library</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="login-wrap"><div class="container"><div class="row justify-content-center"><div class="col-11 col-sm-8 col-md-5 col-lg-4">
  <div class="card shadow border-0"><div class="card-body p-4">
    <h4 class="text-center mb-4"><i class="bi bi-journal-bookmark-fill text-primary"></i> Library Admin</h4>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required autofocus></div>
      <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Login</button>
    </form>
  </div></div>
</div></div></div></body></html>

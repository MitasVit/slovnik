<?php
require 'config.php';
require 'functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) { $error = 'CSRF token invalid.'; }
    else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            $error = 'Vyplň uživatelské jméno a heslo.';
        } else {
            $stmt = $mysqli->prepare('SELECT id, username, password_hash, realname FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $res = $stmt->get_result();
            $user = $res->fetch_assoc();
            $stmt->close();
            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = $user['username'];
                $_SESSION['realname'] = $user['realname'];
                header('Location: admin.php');
                exit;
            } else {
                $error = 'Neplatné přihlašovací údaje.';
            }
        }
    }
}
$csrf = generate_csrf_token();
?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Přihlášení</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light" style="padding:20px;">
<div class="container" style="max-width:640px;">
<h1>Přihlášení</h1>
<?php if($error) echo '<div class="alert alert-danger">'.htmlspecialchars($error).'</div>'; ?>
<form method="post" class="card p-3 bg-secondary text-light">
  <?php echo csrf_input(); ?>
  <div class="mb-3"><label class="form-label">Uživatelské jméno<input name="username" class="form-control"></label></div>
  <div class="mb-3"><label class="form-label">Heslo<input type="password" name="password" class="form-control"></label></div>
  <button class="btn btn-primary">Přihlásit</button>
</form>
</div>
</body></html>

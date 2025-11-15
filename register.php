<?php
require 'config.php';
require 'functions.php';
$can_create = false;
$stmt = $mysqli->prepare('SELECT COUNT(*) as c FROM users');
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();
if ($res && intval($res['c']) === 0) {
    $can_create = true; // allow first user registration
} else {
    $user = currentUser($mysqli);
    if ($user) $can_create = true; // allow logged-in user to create other users
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) { $error = 'CSRF token invalid.'; }
    else {
        $username = trim($_POST['username'] ?? '');
        $realname = trim($_POST['realname'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '' || $realname === '') {
            $error = 'Vyplň všechna pole.';
        } else {
            // check exists
            $stmt = $mysqli->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($r) { $error = 'Uživatel již existuje.'; }
            else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare('INSERT INTO users (username, password_hash, realname) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $username, $hash, $realname);
                $stmt->execute();
                $stmt->close();
                header('Location: admin.php');
                exit;
            }
        }
    }
}
$csrf = generate_csrf_token();
?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Registrace uživatele</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light" style="padding:20px;">
<div class="container" style="max-width:720px;">
<h1>Vytvořit uživatele</h1>
<?php if(!$can_create) { echo '<div class="alert alert-danger">Registrace není povolena.</div>'; exit; } ?>
<?php if($error) echo '<div class="alert alert-danger">'.htmlspecialchars($error).'</div>'; ?>
<form method="post" class="card p-3 bg-secondary text-light">
  <?php echo csrf_input(); ?>
  <div class="mb-3"><label class="form-label">Uživatelské jméno<input name="username" class="form-control"></label></div>
  <div class="mb-3"><label class="form-label">Skutečné jméno<input name="realname" class="form-control"></label></div>
  <div class="mb-3"><label class="form-label">Heslo<input type="password" name="password" class="form-control"></label></div>
  <button class="btn btn-success">Vytvořit</button>
</form>
<p><a href="admin.php">&larr; zpět</a></p>
</div>
</body></html>

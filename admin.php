<?php
require 'functions.php';
$user = currentUser($mysqli);
if (!$user) { header('Location: login.php'); exit; }
?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Admin - Slovník</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light" style="padding:20px;">
<div class="container">
<h1>Správa slovníku</h1>
<p>Přihlášen jako: <?=htmlspecialchars($user['realname'])?> — <a class="text-info" href="logout.php">odhlásit</a></p>
<p>
  <a class="btn btn-success" href="add.php">Přidat nový pojem</a>
  <a class="btn btn-secondary" href="api.php?reload=1">Obnovit cache</a>
  <a class="btn btn-outline-info" href="register.php">Vytvořit uživatele</a>
</p>
<table class="table table-striped table-dark">
<thead><tr><th>ID</th><th>Pojem</th><th>Verze</th><th>Autor</th><th>Akce</th></tr></thead><tbody>
<?php
$list = json_decode(file_get_contents(__DIR__ . '/cache.json'), true);
if ($list) {
    foreach ($list as $p) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars($p['id'] ?? '').'</td>';
        echo '<td>'.htmlspecialchars($p['pojem'] ?? '').'</td>';
        echo '<td>'.htmlspecialchars($p['aktualni_verze'] ?? '').'</td>';
        echo '<td>'.htmlspecialchars(implode(", ", $p['vytvorili'] ?? [])).'</td>';
        echo '<td>
          <a class="btn btn-sm btn-primary" href="edit.php?id='.urlencode($p['id']).'">Upravit</a>
          <form method="post" action="delete.php" style="display:inline-block;margin-left:8px;" onsubmit="return confirm(' . "'Opravdu smazat? Tento krok nelze vrátit.'" . ')">
            '.csrf_input().'
            <input type="hidden" name="id" value="'.htmlspecialchars($p['id']).'">
            <button class="btn btn-sm btn-danger">Smazat</button>
          </form>
        </td>';
        echo '</tr>';
    }
}
?>
</tbody></table>
</div>
</body></html>

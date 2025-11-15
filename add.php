<?php
require 'functions.php';
$user = currentUser($mysqli);
if (!$user) { header('Location: login.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) { $error = 'CSRF token invalid.'; }
    else {
        $pojem = trim($_POST['pojem'] ?? '');
        $id = trim($_POST['id'] ?? '');
        if ($pojem === '' || $id === '') {
            $error = 'Id a pojem jsou povinné.';
        } else {
            if (findFolderById($BASE_DIR, $id) !== null) {
                $error = 'ID již existuje.';
            } else {
                $slug = slugify($pojem) . '-' . $id;
                $folder = $BASE_DIR . '/' . $slug;
                if (!mkdir($folder . '/verze', 0755, true)) {
                    $error = 'Nelze vytvořit složku (permissions).';
                } else {
                    $meta = [
                        'id'=>$id,
                        'pojem'=>$pojem,
                        'vytvorili'=> [$user['realname']],
                        'puvodni_anglicismus'=> $_POST['puvodni'] ?? '',
                        'aktualni_verze'=>'1.0'
                    ];
                    file_put_contents($folder . '/meta.json', json_encode($meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
                    $vyznamy = array_values(array_filter(array_map('trim', explode("\n", $_POST['vyznamy'] ?? ''))));
                    $pouziti = array_values(array_filter(array_map('trim', explode("\n", $_POST['pouziti'] ?? ''))));
                    $vyznamy_struct = array_map(function($v){ return ['vyznam'=>$v,'synonymum_id'=>null]; }, $vyznamy);
                    $ver = [
                        'verze'=>'1.0',
                        'vyznamy_slova'=>$vyznamy_struct,
                        'rod'=>$_POST['rod'] ?? '',
                        'druh'=>$_POST['druh'] ?? '',
                        'pouziti_ve_vete'=>$pouziti,
                        'upravili'=> [$user['realname']]
                    ];
                    file_put_contents($folder . '/verze/1.0.json', json_encode($ver, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
                    buildCache();
                    header('Location: admin.php');
                    exit;
                }
            }
        }
    }
}
$csrf = generate_csrf_token();
?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Přidat pojem</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light" style="padding:20px;">
<div class="container" style="max-width:900px;">
<h1>Přidat nový pojem</h1>
<?php if(isset($error)) echo '<div class="alert alert-danger">'.htmlspecialchars($error).'</div>'; ?>
<form method="post" class="card p-3 bg-secondary text-light">
  <?php echo csrf_input(); ?>
  <div class="mb-3"><label class="form-label">ID (unikátní)<br><input name="id" class="form-control"></label></div>
  <div class="mb-3"><label class="form-label">Pojem<br><input name="pojem" class="form-control"></label></div>
  <div class="mb-3"><label class="form-label">Původní anglicismus (volitelně)<br><input name="puvodni" class="form-control"></label></div>
  <div class="mb-3"><label class="form-label">Významy (každý na nový řádek)<br><textarea name="vyznamy" class="form-control" rows="4"></textarea></label></div>
  <div class="mb-3"><label class="form-label">Použití ve větě (každý na nový řádek)<br><textarea name="pouziti" class="form-control" rows="3"></textarea></label></div>
  <div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Rod<br><input name="rod" class="form-control"></label></div>
    <div class="col-md-6 mb-3"><label class="form-label">Druh<br><input name="druh" class="form-control"></label></div>
  </div>
  <button class="btn btn-success">Vytvořit</button>
</form>
<p><a href="admin.php">&larr; zpět</a></p>
</div>
</body></html>

<?php
require 'functions.php';
$user = currentUser($mysqli, $COOKIE_NAME);
if (!$user) { header('Location: login.php'); exit; }

$id = $_GET['id'] ?? null;
if (!$id) die('Chybí ID.');

$folder = findFolderById($BASE_DIR, $id);
if (!$folder) die('Pojem nenalezen.');

$metaPath = $BASE_DIR . '/' . $folder . '/meta.json';
$meta = json_decode(file_get_contents($metaPath), true);
$verPath = $BASE_DIR . '/' . $folder . '/verze/' . ($meta['aktualni_verze'] ?? '1.0') . '.json';
$ver = json_decode(file_get_contents($verPath), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // basic validation and parsing
    $vyznamy_raw = $_POST['vyznamy'] ?? '';
    $pouziti_raw = $_POST['pouziti'] ?? '';
    $vyznamy = array_values(array_filter(array_map('trim', explode("\n", $vyznamy_raw))));
    $pouziti = array_values(array_filter(array_map('trim', explode("\n", $pouziti_raw))));
    $rod = trim($_POST['rod'] ?? '');
    $druh = trim($_POST['druh'] ?? '');
    // compute new version (increment by 0.1)
    $cur = floatval($meta['aktualni_verze'] ?? '1.0');
    $new = number_format((($cur * 10) + 1) / 10, 1);
    $newPath = $BASE_DIR . '/' . $folder . '/verze/' . $new . '.json';
    $vyznamy_struct = array_map(function($v){ return ['vyznam'=>$v,'synonymum_id'=>null]; }, $vyznamy);
    $data = [
        'verze'=> (string)$new,
        'vyznamy_slova'=> $vyznamy_struct,
        'rod'=> $rod,
        'druh'=> $druh,
        'pouziti_ve_vete'=> $pouziti,
        'upravili'=> [$user['realname']]
    ];
    file_put_contents($newPath, json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    // update meta
    $meta['aktualni_verze'] = (string)$new;
    $meta['upravili'] = $meta['upravili'] ?? [];
    $meta['upravili'][] = $user['realname'];
    file_put_contents($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    // rebuild cache
    buildCache();
    header('Location: admin.php');
    exit;
}

?>
<!doctype html><html lang="cs"><head><meta charset="utf-8"><title>Editovat: <?=htmlspecialchars($meta['pojem'])?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"></head><body style="padding:20px;">
<h1>Upravit: <?=htmlspecialchars($meta['pojem'])?></h1>
<form method="post">
<div class="form-group">
<label>Významy (každý na nový řádek)<br>
<textarea name="vyznamy" class="form-control" rows="4"><?php
echo htmlspecialchars(implode("\n", array_map(function($v){ return $v['vyznam'] ?? $v['text'] ?? ''; }, $ver['vyznamy_slova'] ?? [])));
?></textarea></label></div>

<div class="form-group">
<label>Použití ve větě (každý na nový řádek)<br>
<textarea name="pouziti" class="form-control" rows="4"><?php echo htmlspecialchars(implode("\n", $ver['pouziti_ve_vete'] ?? [])); ?></textarea></label></div>

<div class="form-row">
<div class="form-group col-md-6"><label>Rod<br><input name="rod" class="form-control" value="<?=htmlspecialchars($ver['rod'] ?? '')?>"></label></div>
<div class="form-group col-md-6"><label>Druh<br><input name="druh" class="form-control" value="<?=htmlspecialchars($ver['druh'] ?? '')?>"></label></div>
</div>

<button class="btn btn-primary">Uložit (vytvoří novou verzi)</button>
</form>
<p><a href="admin.php">&larr; zpět</a></p>
</body></html>

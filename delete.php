<?php
require 'functions.php';
$user = currentUser($mysqli);
if (!$user) { header('Location: login.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { die('Invalid'); }
if (!validate_csrf()) die('Invalid CSRF');
$id = $_POST['id'] ?? '';
$folder = findFolderById($BASE_DIR, $id);
if (!$folder) die('Not found');
$path = $BASE_DIR . '/' . $folder;
// recursive delete
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object == '.' || $object == '..') continue;
        $full = $dir . '/' . $object;
        if (is_dir($full)) rrmdir($full); else @unlink($full);
    }
    @rmdir($dir);
}
rrmdir($path);
buildCache();
header('Location: admin.php');
exit;
?>
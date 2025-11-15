<?php
require_once __DIR__ . '/config.php';

// Build cache from data/ into cache.json
function buildCache($baseDir=null, $cacheFile=null) {
    global $BASE_DIR, $CACHE_FILE;
    $base = $baseDir ?? $BASE_DIR;
    $cachePath = $cacheFile ?? $CACHE_FILE;
    $folders = scandir($base);
    $all = [];
    foreach ($folders as $f) {
        if ($f[0] === '.' || !is_dir($base . '/' . $f)) continue;
        $metaPath = $base . '/' . $f . '/meta.json';
        if (!file_exists($metaPath)) continue;
        $meta = json_decode(file_get_contents($metaPath), true);
        if (!isset($meta['aktualni_verze'])) continue;
        $verzePath = $base . '/' . $f . '/verze/' . $meta['aktualni_verze'] . '.json';
        if (!file_exists($verzePath)) continue;
        $verze = json_decode(file_get_contents($verzePath), true);
        $merged = array_merge($meta, $verze);
        $merged['_folder'] = $f;
        $all[] = $merged;
    }
    file_put_contents($cachePath, json_encode($all, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    return $all;
}

function loadCache($baseDir=null, $cacheFile=null) {
    global $BASE_DIR, $CACHE_FILE;
    $base = $baseDir ?? $BASE_DIR;
    $cachePath = $cacheFile ?? $CACHE_FILE;
    if (file_exists($cachePath)) {
        $json = json_decode(file_get_contents($cachePath), true);
        if ($json !== null) return $json;
    }
    return buildCache($base, $cachePath);
}

function findFolderById($baseDir, $id) {
    $folders = scandir($baseDir);
    foreach ($folders as $f) {
        if ($f[0] === '.' || !is_dir($baseDir . '/' . $f)) continue;
        $metaPath = $baseDir . '/' . $f . '/meta.json';
        if (!file_exists($metaPath)) continue;
        $meta = json_decode(file_get_contents($metaPath), true);
        if (isset($meta['id']) && $meta['id'] == $id) return $f;
    }
    return null;
}

// Session-based current user (reads $_SESSION['user'])
function currentUser($mysqli) {
    if (!isset($_SESSION['user'])) return null;
    $username = $_SESSION['user'];
    $stmt = $mysqli->prepare("SELECT id, username, realname FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();
    return $user ?: null;
}

// CSRF helpers
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_input() {
    $t = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars($t).'">';
}
function validate_csrf() {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function slugify($text) {
    // simple slug
    $text = preg_replace('~[^\pL0-9]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-a-zA-Z0-9]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    if (empty($text)) return 'n-a';
    return $text;
}
?>

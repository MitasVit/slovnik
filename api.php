<?php
header("Content-Type: application/json; charset=UTF-8");
require 'functions.php';

$cache = loadCache();

if (isset($_GET['reload'])) {
    $cache = buildCache();
    echo json_encode(['status'=>'cache rebuilt','count'=>count($cache)]);
    exit;
}

if (isset($_GET['list'])) {
    $list = array_map(function($p){
        return [
            'id'=>$p['id'] ?? null,
            'pojem'=>$p['pojem'] ?? null,
            'aktualni_verze'=>$p['aktualni_verze'] ?? null,
            '_folder'=>$p['_folder'] ?? null
        ];
    }, $cache);
    echo json_encode($list);
    exit;
}

if (isset($_GET['term'])) {
    $term = strtolower($_GET['term']);
    $found = array_filter($cache, function($p) use ($term){
        return strtolower($p['pojem'] ?? '') === $term || strtolower($p['id'] ?? '') === $term;
    });
    echo json_encode(array_values($found));
    exit;
}

if (isset($_GET['q'])) {
    $q = strtolower($_GET['q']);
    $results = array_filter($cache, function($p) use ($q){
        if (strpos(strtolower($p['pojem'] ?? ''), $q) !== false) return true;
        if (isset($p['vyznamy_slova'])) {
            foreach ($p['vyznamy_slova'] as $v) {
                $text = strtolower($v['vyznam'] ?? $v['text'] ?? '');
                if (strpos($text, $q) !== false) return true;
            }
        }
        return false;
    });
    echo json_encode(array_values($results));
    exit;
}

echo json_encode(['error'=>'Použij ?list= nebo ?term= nebo ?q=']);
?>

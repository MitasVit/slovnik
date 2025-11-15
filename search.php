<?php
$q = $_GET['q'] ?? '';
?>
<!doctype html>
<html lang="cs"><head><meta charset="utf-8"><title>Hledání: <?=htmlspecialchars($q)?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-dark text-light" style="padding:20px;">
<div class="container">
<a class="text-info" href="index.php">&larr; Zpět na hlavní</a>
<h1>Výsledky hledání: <?=htmlspecialchars($q)?></h1>
<div id="results">Načítám...</div>
<script>
const q = <?= json.dumps(q) ?>;
fetch('api.php?q=' + encodeURIComponent(q)).then(r=>r.json()).then(data=>{
    const d = document.getElementById('results');
    if (!data || data.length===0) { d.innerHTML = '<p>Nic nenalezeno.</p>'; return; }
    d.innerHTML = data.map(p=>{
        const meanings = (p.vyznamy_slova || []).map(v=>v.vyznam || v.text || '').join('; ');
        return `<div class="card mb-2 p-3 bg-secondary text-light">
            <h4>${p.pojem} <small class="text-muted">v${p.aktualni_verze}</small></h4>
            <p><strong>Významy:</strong> ${meanings}</p>
            <p><a href="edit.php?id=${encodeURIComponent(p.id)}" class="btn btn-sm btn-outline-light">Upravit / detail</a></p>
        </div>`;
    }).join('');
});
</script>
</div></body></html>

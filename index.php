<!DOCTYPE html>
<html lang="cs">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Slovník</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      body { background:#0d1117; color:#c9d1d9; padding-top:80px; }
      .card { background:#010409; border-color:#30363d; }
      .dictionary-entry:hover { transform: translateY(-3px); transition: 0.2s; box-shadow: 0 6px 18px rgba(0,0,0,0.6); }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="index.php">Slovník</a>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          </ul>
        </div>
        <form class="d-flex" role="search" onsubmit="doSearch(event)">
          <input id="live-search" class="form-control me-2" type="search" placeholder="Hledej..." aria-label="Search">
          <button class="btn btn-outline-success" type="button" onclick="doSearch()">Hledat</button>
        </form>
      </div>
    </nav>

    <main class="container">
      <header class="py-4 text-center">
        <h1>Vítejte v našem slovníku!</h1>
        <p class="lead">Kolekce českých slov a neologismů.</p>
      </header>

      <section>
        <div class="row">
          <div class="col-md-8">
            <h3>Žádost A–Z</h3>
            <div id="dictionary-list"></div>
          </div>
          <div class="col-md-4">
            <h5>Rychlé hledání</h5>
            <div id="live-results"></div>
          </div>
        </div>
      </section>
    </main>

    <script>
      // live search with debounce
      let debounceTimer = null;
      function doSearch(e) {
        if (e && e.preventDefault) e.preventDefault();
        const q = document.getElementById('live-search').value.trim();
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(()=> {
          if (!q) { document.getElementById('live-results').innerHTML=''; return; }
          fetch('api.php?q=' + encodeURIComponent(q)).then(r=>r.json()).then(data=>{
            const node = document.getElementById('live-results');
            if (!data || data.length===0) { node.innerHTML = '<p>Nic nenalezeno.</p>'; return; }
            node.innerHTML = data.slice(0,10).map(p=> {
              const meanings = (p.vyznamy_slova || []).map(v=>v.vyznam || v.text || '').join('; ');
              return `<div class="card mb-2 p-2"><b>${p.pojem}</b><div class="small text-muted">v${p.aktualni_verze}</div><div>${meanings}</div><div><a class="btn btn-sm btn-link" href="edit.php?id=${encodeURIComponent(p.id)}">Detail</a></div></div>`;
            }).join('');
          });
        }, 250);
      }

      // load A-Z list
      fetch('api.php?list=1').then(r=>r.json()).then(data=>{
        const container = document.getElementById('dictionary-list');
        if (!data || data.length===0) { container.innerHTML = '<p>Žádná data.</p>'; return; }
        const groups = {};
        data.forEach(item=>{
            const first = (item.pojem || '?').charAt(0).toUpperCase();
            if (!/[A-ZÁČĎÉĚÍŇÓŘŠŤÚŮÝ]/.test(first)) { groups['#'] = groups['#']||[]; groups['#'].push(item); }
            else { groups[first] = groups[first]||[]; groups[first].push(item); }
        });
        const letters = Object.keys(groups).sort();
        let html = '';
        letters.forEach(letter=>{
            html += `<h4 class="text-light">${letter}</h4><div class="list-group mb-3">`;
            groups[letter].forEach(it=>{
                html += `<a class="list-group-item list-group-item-action" href="edit.php?id=${encodeURIComponent(it.id)}">${it.pojem} <small class="text-muted">v${it.aktualni_verze}</small></a>`;
            });
            html += '</div>';
        });
        container.innerHTML = html;
      });
    </script>
  </body>
</html>

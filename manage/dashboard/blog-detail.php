<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<?php
// ══════════════════════════════════════════════════════════════
// Connexion & récupération de l'article
// ══════════════════════════════════════════════════════════════
require_once __DIR__ . '../../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
Connexion();
global $conDB;

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: blog.php'); exit; }

$stmt = $conDB->prepare("SELECT * FROM blog WHERE id_blog = ? AND statut = 'publié'");
$stmt->execute([$id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) { header('Location: blog.php'); exit; }

// ── Incrémenter les vues (anti-doublon IP + jour) ─────────────
$ip       = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ip       = trim(explode(',', $ip)[0]);
$today    = date('Y-m-d');

try {
    // Tenter d'insérer une vue unique
    $ins = $conDB->prepare(
        "INSERT IGNORE INTO blog_vues (id_blog, ip, date_vue) VALUES (?, ?, ?)"
    );
    $ins->execute([$id, $ip, $today]);

    // Si une ligne a bien été insérée (nouvelle vue), on incrémente
    if ($ins->rowCount() > 0) {
        $conDB->prepare("UPDATE blog SET vues = vues + 1 WHERE id_blog = ?")->execute([$id]);
        $blog['vues']++;
    }
} catch (Exception $e) {
    // silencieux — les vues ne bloquent pas la page
}

// ── Articles similaires (même catégorie, max 3) ───────────────
$sim_stmt = $conDB->prepare(
    "SELECT id_blog, titre, intro, photo_couverture, auteur, vues, created_at
     FROM blog WHERE statut = 'publié' AND categorie = ? AND id_blog != ?
     ORDER BY created_at DESC LIMIT 3"
);
$sim_stmt->execute([$blog['categorie'], $id]);
$similaires = $sim_stmt->fetchAll(PDO::FETCH_ASSOC);

function readTime(string $contenu): string {
    $words = str_word_count(strip_tags($contenu));
    return max(1, round($words / 200)) . ' min';
}
?>
  <title><?= htmlspecialchars($blog['titre']) ?> — JCB Blog</title>
  <meta name="description" content="<?= htmlspecialchars(substr($blog['intro'], 0, 160)) ?>">
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue:      #2f49d1;
      --blue-l:    rgba(47,73,209,0.08);
      --red:       #f26464;
      --dark:      #0e0e1a;
      --mid:       #767676;
      --muted:     #b9b9b9;
      --border:    #eeeeee;
      --bg:        #f4f5fb;
      --white:     #ffffff;
      --font-serif: 'Playfair Display', Georgia, serif;
      --font-sans:  'DM Sans', system-ui, sans-serif;
    }
    html { scroll-behavior: smooth; }
    body { font-family: var(--font-sans); background: var(--bg); color: var(--dark); }
    a    { color: inherit; text-decoration: none; }
    img  { max-width: 100%; display: block; }

    /* ── Navbar ── */
    .pub-nav {
      position: sticky; top: 0; z-index: 100;
      background: rgba(255,255,255,0.92); backdrop-filter: blur(14px);
      border-bottom: 1px solid var(--border);
      padding: 0 clamp(16px,5vw,60px); height: 64px;
      display: flex; align-items: center; justify-content: space-between;
    }
    .pub-nav__logo { font-family: var(--font-serif); font-size: 20px; font-weight: 700; color: var(--blue); display: flex; align-items: center; gap: 10px; }
    .pub-nav__logo img { width: 36px; height: 36px; border-radius: 8px; }
    .pub-nav__links { display: flex; align-items: center; gap: 24px; }
    .pub-nav__links a { font-size: 14px; font-weight: 500; color: var(--mid); transition: color 0.2s; }
    .pub-nav__links a:hover { color: var(--blue); }
    .pub-nav__cta { padding: 8px 20px; border-radius: 8px; background: var(--blue); color: #fff !important; font-weight: 600; font-size: 13px; }

    /* ── Hero image ── */
    .article-hero {
      width: 100%; height: clamp(260px, 40vw, 480px);
      object-fit: cover; display: block;
    }
    .article-hero-placeholder {
      height: clamp(200px, 30vw, 380px);
      background: linear-gradient(135deg, #1a2a8f 0%, #0e0e1a 100%);
    }

    /* ── Layout ── */
    .article-layout {
      max-width: 1160px; margin: 0 auto;
      padding: 0 clamp(16px,4vw,40px);
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 36px; align-items: start;
      padding-top: 48px; padding-bottom: 72px;
    }
    @media (max-width: 900px) { .article-layout { grid-template-columns: 1fr; } }

    /* ── Article header ── */
    .article-cat {
      display: inline-flex; align-items: center;
      background: var(--blue-l); color: var(--blue);
      font-size: 12px; font-weight: 700; letter-spacing: 0.5px;
      text-transform: uppercase; padding: 4px 12px; border-radius: 20px;
      margin-bottom: 16px;
    }
    .article-title {
      font-family: var(--font-serif);
      font-size: clamp(26px,4vw,44px); font-weight: 800;
      line-height: 1.15; color: var(--dark); margin-bottom: 18px;
    }
    .article-meta {
      display: flex; flex-wrap: wrap; align-items: center; gap: 18px;
      padding-bottom: 20px; margin-bottom: 28px;
      border-bottom: 1px solid var(--border);
    }
    .meta-item {
      display: flex; align-items: center; gap: 6px;
      font-size: 13px; color: var(--mid); font-weight: 500;
    }
    .meta-item svg { width: 15px; height: 15px; }
    .meta-item.views { color: var(--blue); font-weight: 700; }

    /* ── Article body ── */
    .article-body { font-size: 16px; line-height: 1.85; color: #3a3a4e; }
    .article-body h1, .article-body h2, .article-body h3 {
      font-family: var(--font-serif); color: var(--dark);
      margin: 32px 0 12px; line-height: 1.25;
    }
    .article-body h2 { font-size: 26px; }
    .article-body h3 { font-size: 20px; }
    .article-body p  { margin-bottom: 18px; }
    .article-body ul, .article-body ol { padding-left: 22px; margin-bottom: 18px; }
    .article-body li { margin-bottom: 6px; }
    .article-body blockquote {
      border-left: 4px solid var(--blue); margin: 24px 0;
      padding: 14px 20px; background: var(--blue-l); border-radius: 0 10px 10px 0;
      font-style: italic; color: var(--mid);
    }
    .article-body img { border-radius: 10px; margin: 20px 0; max-width: 100%; }
    .article-body a   { color: var(--blue); text-decoration: underline; }
    .article-body code {
      background: #eff0f6; padding: 2px 6px; border-radius: 4px;
      font-size: 14px; font-family: monospace;
    }
    .article-body pre {
      background: #1e1e2e; color: #cdd6f4; padding: 16px 20px;
      border-radius: 10px; overflow-x: auto; margin: 20px 0;
    }

    /* ── Tags ── */
    .article-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border); }
    .article-tag  { padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; background: var(--blue-l); color: var(--blue); }

    /* ── Share ── */
    .share-bar {
      display: flex; align-items: center; gap: 10px;
      margin-top: 28px; flex-wrap: wrap;
    }
    .share-bar span { font-size: 13px; font-weight: 600; color: var(--mid); }
    .share-btn {
      display: flex; align-items: center; gap: 6px;
      padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
      border: none; cursor: pointer; transition: 0.2s;
    }
    .share-btn.copy   { background: var(--blue-l); color: var(--blue); }
    .share-btn.copy:hover { background: rgba(47,73,209,0.14); }
    .share-btn svg { width: 15px; height: 15px; }

    /* ── Sidebar ── */
    .sidebar-card { background: var(--white); border-radius: 14px; padding: 20px; margin-bottom: 16px; box-shadow: 0 2px 12px rgba(14,14,26,0.06); }
    .sidebar-card:last-child { margin-bottom: 0; }
    .sidebar-card__title { font-family: var(--font-serif); font-size: 16px; font-weight: 700; color: var(--dark); margin-bottom: 16px; }

    /* Auteur card */
    .author-card { display: flex; align-items: center; gap: 14px; }
    .author-avatar {
      width: 52px; height: 52px; border-radius: 50%;
      background: var(--blue-l); display: flex; align-items: center; justify-content: center;
      color: var(--blue); flex-shrink: 0; font-family: var(--font-serif); font-size: 20px; font-weight: 700;
    }
    .author-name { font-weight: 700; font-size: 15px; color: var(--dark); }
    .author-role { font-size: 12px; color: var(--muted); margin-top: 2px; }

    /* Stats card */
    .stat-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--border); }
    .stat-row:last-child { border-bottom: none; }
    .stat-label { font-size: 13px; color: var(--mid); display: flex; align-items: center; gap: 6px; }
    .stat-label svg { width: 14px; height: 14px; }
    .stat-value { font-size: 14px; font-weight: 700; color: var(--dark); }
    .stat-value.blue { color: var(--blue); }

    /* Similaires */
    .sim-item { display: flex; gap: 12px; margin-bottom: 14px; text-decoration: none; }
    .sim-item:last-child { margin-bottom: 0; }
    .sim-thumb { width: 64px; height: 64px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .sim-thumb-ph { width: 64px; height: 64px; border-radius: 8px; background: var(--blue-l); display: flex; align-items: center; justify-content: center; color: var(--blue); flex-shrink: 0; }
    .sim-info { display: flex; flex-direction: column; justify-content: center; gap: 4px; }
    .sim-title { font-size: 13px; font-weight: 600; color: var(--dark); line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; transition: color 0.2s; }
    .sim-item:hover .sim-title { color: var(--blue); }
    .sim-meta  { font-size: 11px; color: var(--muted); display: flex; align-items: center; gap: 5px; }

    /* ── Back link ── */
    .back-link { display: inline-flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 600; color: var(--mid); margin-bottom: 24px; transition: color 0.2s; }
    .back-link:hover { color: var(--blue); }
    .back-link svg { width: 15px; height: 15px; }

    /* ── Progress bar ── */
    .read-progress { position: fixed; top: 64px; left: 0; width: 0%; height: 3px; background: var(--blue); z-index: 200; transition: width 0.1s linear; border-radius: 0 2px 2px 0; }

    @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
    .anim { animation: fadeUp 0.5s ease both; }
  </style>
</head>
<body>

<!-- Barre de progression lecture -->
<div class="read-progress" id="readProgress"></div>

<!-- Navbar -->
<nav class="pub-nav">
  <a href="../index.php" class="pub-nav__logo">
    <img src="../assets/img/svg/logo.svg" alt="JCB" onerror="this.style.display='none'">
    Juste Cœur BeauBrun
  </a>
  <div class="pub-nav__links">
    <a href="../index.php">Accueil</a>
    <a href="blog.php">Blog</a>
    <a href="../index.php#contact">Contact</a>
    <a href="../index.php#don" class="pub-nav__cta">Faire un don</a>
  </div>
</nav>

<!-- Hero image -->
<?php if (!empty($blog['photo_couverture'])): ?>
  <img class="article-hero" src="../<?= htmlspecialchars($blog['photo_couverture']) ?>" alt="<?= htmlspecialchars($blog['titre']) ?>">
<?php else: ?>
  <div class="article-hero-placeholder"></div>
<?php endif; ?>

<div class="article-layout">

  <!-- ══ Contenu principal ════════════════════════════════════ -->
  <article class="anim">

    <a href="blog.php" class="back-link">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      Retour aux articles
    </a>

    <span class="article-cat"><?= htmlspecialchars($blog['categorie']) ?></span>
    <h1 class="article-title"><?= htmlspecialchars($blog['titre']) ?></h1>

    <!-- Meta -->
    <div class="article-meta">
      <div class="meta-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?= htmlspecialchars($blog['auteur']) ?>
      </div>
      <div class="meta-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <?= date('d M Y', strtotime($blog['created_at'])) ?>
      </div>
      <div class="meta-item">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <?= readTime($blog['contenu']) ?> de lecture
      </div>
      <div class="meta-item views">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <?= number_format($blog['vues']) ?> lecture<?= $blog['vues'] > 1 ? 's' : '' ?>
      </div>
    </div>

    <!-- Corps de l'article -->
    <div class="article-body" id="articleBody">
      <?= $blog['contenu'] /* HTML généré par Quill — déjà stocké en HTML */ ?>
    </div>

    <!-- Tags -->
    <?php if (!empty($blog['tags'])): ?>
    <div class="article-tags">
      <?php foreach (explode(',', $blog['tags']) as $tag): ?>
        <?php $tag = trim($tag); if ($tag === '') continue; ?>
        <span class="article-tag"><?= htmlspecialchars($tag) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Partager -->
    <div class="share-bar">
      <span>Partager :</span>
      <button class="share-btn copy" onclick="copyLink()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        <span id="copyLabel">Copier le lien</span>
      </button>
    </div>

  </article>

  <!-- ══ Sidebar ══════════════════════════════════════════════ -->
  <aside>

    <!-- Auteur -->
    <div class="sidebar-card anim">
      <div class="sidebar-card__title">À propos de l'auteur</div>
      <div class="author-card">
        <div class="author-avatar"><?= mb_strtoupper(mb_substr($blog['auteur'], 0, 1)) ?></div>
        <div>
          <div class="author-name"><?= htmlspecialchars($blog['auteur']) ?></div>
          <div class="author-role">Contributeur JCB</div>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="sidebar-card anim">
      <div class="sidebar-card__title">Statistiques</div>
      <div class="stat-row">
        <span class="stat-label">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          Lectures
        </span>
        <span class="stat-value blue"><?= number_format($blog['vues']) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-label">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Temps de lecture
        </span>
        <span class="stat-value"><?= readTime($blog['contenu']) ?></span>
      </div>
      <div class="stat-row">
        <span class="stat-label">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Publié le
        </span>
        <span class="stat-value"><?= date('d/m/Y', strtotime($blog['created_at'])) ?></span>
      </div>
      <?php if (!empty($blog['updated_at']) && $blog['updated_at'] !== $blog['created_at']): ?>
      <div class="stat-row">
        <span class="stat-label">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Mis à jour le
        </span>
        <span class="stat-value"><?= date('d/m/Y', strtotime($blog['updated_at'])) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Articles similaires -->
    <?php if (!empty($similaires)): ?>
    <div class="sidebar-card anim">
      <div class="sidebar-card__title">Articles similaires</div>
      <?php foreach ($similaires as $s): ?>
        <a href="blog_detail.php?id=<?= $s['id_blog'] ?>" class="sim-item">
          <?php if (!empty($s['photo_couverture'])): ?>
            <img class="sim-thumb" src="../<?= htmlspecialchars($s['photo_couverture']) ?>" alt="">
          <?php else: ?>
            <div class="sim-thumb-ph">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            </div>
          <?php endif; ?>
          <div class="sim-info">
            <div class="sim-title"><?= htmlspecialchars($s['titre']) ?></div>
            <div class="sim-meta">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <?= number_format($s['vues']) ?> vue<?= $s['vues'] > 1 ? 's' : '' ?>
              &nbsp;·&nbsp;
              <?= date('d M Y', strtotime($s['created_at'])) ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </aside>
</div>

<script>
  // ── Barre de progression de lecture ──────────────────────────
  const bar  = document.getElementById('readProgress');
  const body = document.getElementById('articleBody');
  window.addEventListener('scroll', () => {
    const bodyTop    = body.getBoundingClientRect().top + window.scrollY;
    const bodyBottom = bodyTop + body.offsetHeight;
    const scrolled   = window.scrollY + window.innerHeight;
    const pct = Math.min(100, Math.max(0,
      ((window.scrollY - bodyTop) / (bodyBottom - bodyTop - window.innerHeight)) * 100
    ));
    bar.style.width = pct + '%';
  });

  // ── Copier le lien ────────────────────────────────────────────
  function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
      const lbl = document.getElementById('copyLabel');
      lbl.textContent = 'Copié !';
      setTimeout(() => lbl.textContent = 'Copier le lien', 2000);
    });
  }
</script>

<script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
</body>
</html>
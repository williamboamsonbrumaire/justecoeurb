<?php
require_once __DIR__ . '/model/blog-crud.php';   // ← intègre config + Connexion() + toutes les fonctions

// Pagination
$limit      = 9;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $limit;

// Filtre catégorie
$catFilter  = trim($_GET['cat'] ?? '');

// Filtre statut (admin voit tout)
$statutFilter = trim($_GET['statut'] ?? ''); // '', 'publié', 'brouillon'

// Données — tous les articles (publiés + brouillons)
$blogs = getAllBlogs($conDB, $limit, $offset, $statutFilter);

// Filtre côté PHP si catégorie choisie
if ($catFilter !== '') {
    $blogs = array_values(array_filter($blogs, fn($b) => $b['categorie'] === $catFilter));
}

// Comptage total pour pagination
try {
    $whereCount = $statutFilter !== ''
        ? "WHERE statut = " . $conDB->quote($statutFilter)
        : "";
    $stmtCount  = $conDB->query("SELECT COUNT(*) FROM blog $whereCount");
    $totalBlogs = (int) $stmtCount->fetchColumn();
} catch (Exception $e) {
    $totalBlogs = 0;
}
$totalPages = (int) ceil($totalBlogs / $limit);

// Comptage par statut pour les badges
try {
    $stmtStats = $conDB->query("SELECT statut, COUNT(*) as nb FROM blog GROUP BY statut");
    $statsRaw  = $stmtStats->fetchAll(PDO::FETCH_ASSOC);
    $stats = ['publié' => 0, 'brouillon' => 0];
    foreach ($statsRaw as $s) $stats[$s['statut']] = (int)$s['nb'];
    $totalAll = array_sum($stats);
} catch (Exception $e) {
    $stats = ['publié' => 0, 'brouillon' => 0];
    $totalAll = $totalBlogs;
}

// Catégories disponibles pour le filtre
try {
    $stmtCat    = $conDB->query("SELECT DISTINCT categorie FROM blog WHERE categorie IS NOT NULL ORDER BY categorie");
    $categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
}

// Message flash (après delete/update)
$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog | Juste Cœur BeauBrun</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>

    /* ═══════════════════════════════════════════
       FLASH ALERTS
    ═══════════════════════════════════════════ */
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-success { background: rgba(75,222,151,0.12); color: #27a96c; border: 1px solid rgba(75,222,151,0.3); }
    .alert-danger  { background: rgba(242,100,100,0.10); color: #e04b4b; border: 1px solid rgba(242,100,100,0.25); }

    /* ═══════════════════════════════════════════
       HERO BANNER
    ═══════════════════════════════════════════ */
    .blog-hero {
      background: linear-gradient(135deg, #2f49d1 0%, #0061f7 55%, #5f2eea 100%);
      border-radius: 16px;
      padding: 38px 36px;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      overflow: hidden;
      position: relative;
    }
    .blog-hero::before {
      content: '';
      position: absolute;
      width: 320px; height: 320px;
      border-radius: 50%;
      background: rgba(255,255,255,.06);
      right: -70px; top: -90px;
      pointer-events: none;
    }
    .blog-hero::after {
      content: '';
      position: absolute;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(255,255,255,.04);
      right: 140px; bottom: -70px;
      pointer-events: none;
    }
    .blog-hero__text h1 {
      font-size: 26px;
      font-weight: 800;
      color: #fff;
      letter-spacing: .4px;
      margin-bottom: 8px;
      line-height: 1.3;
    }
    .blog-hero__text p {
      font-size: 13.5px;
      color: rgba(255,255,255,.72);
      line-height: 1.65;
      max-width: 400px;
    }
    .blog-hero__actions {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 12px;
      flex-shrink: 0;
      position: relative;
      z-index: 1;
    }
    .blog-hero__stats {
      display: flex;
      gap: 16px;
    }
    .blog-hero__stat {
      text-align: center;
      background: rgba(255,255,255,.13);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 14px;
      padding: 14px 22px;
      backdrop-filter: blur(6px);
    }
    .blog-hero__stat strong {
      display: block;
      font-size: 28px;
      font-weight: 800;
      color: #fff;
      line-height: 1;
    }
    .blog-hero__stat span {
      font-size: 10px;
      color: rgba(255,255,255,.6);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 5px;
      display: block;
    }
    .btn-new-article {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #fff;
      color: #2f49d1;
      font-size: 13px;
      font-weight: 700;
      padding: 10px 20px;
      border-radius: 10px;
      text-decoration: none;
      transition: all .2s;
      white-space: nowrap;
      box-shadow: 0 4px 14px rgba(0,0,0,.15);
    }
    .btn-new-article:hover { background: #f0f3ff; transform: translateY(-1px); }
    @media (max-width: 768px) {
      .blog-hero { flex-direction: column; align-items: flex-start; }
      .blog-hero__actions { align-items: flex-start; width: 100%; }
      .blog-hero__stats { width: 100%; }
      .blog-hero__stat { flex: 1; }
    }

    /* ═══════════════════════════════════════════
       TOOLBAR (filtres + statut tabs)
    ═══════════════════════════════════════════ */
    .blog-toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .statut-tabs {
      display: flex;
      align-items: center;
      gap: 4px;
      background: #f4f5fb;
      border-radius: 10px;
      padding: 4px;
    }
    .statut-tab {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 7px;
      font-size: 12px;
      font-weight: 600;
      color: #767676;
      text-decoration: none;
      transition: all .2s;
      white-space: nowrap;
    }
    .statut-tab:hover { color: #2f49d1; }
    .statut-tab.active { background: #fff; color: #171717; box-shadow: 0 1px 6px rgba(0,0,0,.08); }
    .statut-tab .tab-count {
      font-size: 10px;
      font-weight: 700;
      background: #e8eaf8;
      color: #2f49d1;
      padding: 1px 6px;
      border-radius: 10px;
    }
    .statut-tab.active .tab-count { background: #2f49d1; color: #fff; }

    /* FILTER CHIPS (catégorie) */
    .blog-filter-bar {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .blog-filter-bar__label {
      font-size: 12px;
      font-weight: 700;
      color: #b9b9b9;
      text-transform: uppercase;
      letter-spacing: .8px;
      margin-right: 4px;
    }
    .filter-chip {
      display: inline-flex;
      align-items: center;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      border: 1.5px solid #EEEEEE;
      background: #fff;
      color: #767676;
      cursor: pointer;
      text-decoration: none;
      transition: all .2s;
    }
    .filter-chip:hover { border-color: #2f49d1; color: #2f49d1; }
    .filter-chip.active {
      background: #2f49d1;
      border-color: #2f49d1;
      color: #fff;
      box-shadow: 0 3px 10px rgba(47,73,209,.25);
    }

    /* ═══════════════════════════════════════════
       BLOG GRID
    ═══════════════════════════════════════════ */
    .blog-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 22px;
    }
    @media (max-width: 1100px) { .blog-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 600px)  { .blog-grid { grid-template-columns: 1fr; } }

    /* ═══════════════════════════════════════════
       BLOG CARD
    ═══════════════════════════════════════════ */
    .blog-card {
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      border: 1.5px solid #f0f1f8;
      box-shadow: 0 2px 14px rgba(47,73,209,.06);
      display: flex;
      flex-direction: column;
      transition: transform .3s, box-shadow .3s, border-color .3s;
      position: relative;
    }
    .blog-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 14px 36px rgba(47,73,209,.11);
      border-color: #c8d0f5;
    }
    /* Brouillon : bordure en tirets orangée */
    .blog-card--draft {
      border-style: dashed;
      border-color: #ffe0a3;
    }
    .blog-card--draft:hover { border-color: #ffb648; }

    /* ── Vignette ── */
    .blog-card__thumb {
      position: relative;
      width: 100%;
      padding-top: 52%;
      overflow: hidden;
      background: #eff0f6;
      flex-shrink: 0;
    }
    .blog-card__thumb img {
      position: absolute;
      inset: 0;
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform .45s ease;
    }
    .blog-card:hover .blog-card__thumb img { transform: scale(1.05); }

    .blog-card__no-img {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #eff0f6 0%, #dde2f7 100%);
    }
    .blog-card__no-img svg { width: 48px; height: 48px; color: #2f49d1; opacity: .13; }

    /* Badge catégorie */
    .blog-card__cat {
      position: absolute;
      top: 10px; left: 10px;
      background: #2f49d1;
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .8px;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 20px;
      box-shadow: 0 2px 8px rgba(47,73,209,.4);
      z-index: 1;
    }

    /* ── BADGE STATUT ── */
    .blog-card__status {
      position: absolute;
      top: 10px; right: 10px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .6px;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 20px;
      z-index: 1;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .blog-card__status--published {
      background: rgba(75,222,151,.15);
      color: #27a96c;
      border: 1px solid rgba(75,222,151,.4);
      backdrop-filter: blur(4px);
    }
    .blog-card__status--draft {
      background: rgba(255,182,72,.18);
      color: #c97b00;
      border: 1px solid rgba(255,182,72,.45);
      backdrop-filter: blur(4px);
    }
    .blog-card__status-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      display: inline-block;
      flex-shrink: 0;
    }
    .blog-card__status--published .blog-card__status-dot { background: #4bde97; }
    .blog-card__status--draft    .blog-card__status-dot { background: #ffb648; animation: pulse-dot 1.6s ease-in-out infinite; }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; }
      50%       { opacity: .35; }
    }

    /* Date en overlay */
    .blog-card__date-badge {
      position: absolute;
      bottom: 10px; right: 10px;
      background: rgba(0,0,0,.42);
      backdrop-filter: blur(4px);
      color: #fff;
      font-size: 10px;
      font-weight: 500;
      padding: 3px 10px;
      border-radius: 20px;
      z-index: 1;
    }

    /* ── Corps ── */
    .blog-card__body {
      padding: 16px 18px 18px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    .blog-card__title {
      font-size: 14px;
      font-weight: 700;
      color: #171717;
      line-height: 1.45;
      margin-bottom: 8px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      transition: color .2s;
    }
    .blog-card:hover .blog-card__title { color: #2f49d1; }

    .blog-card__intro {
      font-size: 12px;
      line-height: 1.65;
      color: #767676;
      margin-bottom: 14px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
    }

    /* ── Footer carte ── */
    .blog-card__footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding-top: 12px;
      border-top: 1px solid #f0f1f8;
    }
    .blog-card__author {
      display: flex;
      align-items: center;
      gap: 7px;
      min-width: 0;
    }
    .blog-card__avatar {
      width: 28px; height: 28px;
      border-radius: 50%;
      background: linear-gradient(135deg, #2f49d1, #5f2eea);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      flex-shrink: 0;
    }
    .blog-card__author-name {
      font-size: 11.5px;
      font-weight: 600;
      color: #171717;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* ── Actions (Edit / Delete) ── */
    .blog-card__actions {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }
    .btn-action {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px; height: 32px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      transition: all .2s;
      text-decoration: none;
      flex-shrink: 0;
    }
    .btn-action svg { width: 14px; height: 14px; }
    .btn-action--edit {
      background: rgba(47,73,209,.08);
      color: #2f49d1;
    }
    .btn-action--edit:hover {
      background: #2f49d1;
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(47,73,209,.3);
    }
    .btn-action--delete {
      background: rgba(242,100,100,.08);
      color: #e04b4b;
    }
    .btn-action--delete:hover {
      background: #e04b4b;
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(242,100,100,.3);
    }

    /* ═══════════════════════════════════════════
       FEATURED CARD (1ère, pleine largeur)
    ═══════════════════════════════════════════ */
    .blog-card--featured {
      grid-column: 1 / -1;
      flex-direction: row;
    }
    .blog-card--featured .blog-card__thumb {
      width: 40%;
      padding-top: 0;
      min-height: 240px;
      flex-shrink: 0;
    }
    .blog-card--featured .blog-card__body { padding: 24px 28px; justify-content: center; }
    .blog-card--featured .blog-card__title {
      font-size: 18px;
      -webkit-line-clamp: 3;
      margin-bottom: 10px;
    }
    .blog-card--featured .blog-card__intro { -webkit-line-clamp: 4; font-size: 13px; }
    @media (max-width: 900px) {
      .blog-card--featured { flex-direction: column; }
      .blog-card--featured .blog-card__thumb { width: 100%; padding-top: 50%; min-height: 0; }
    }
    @media (max-width: 600px) { .blog-card--featured { grid-column: 1; } }

    /* ═══════════════════════════════════════════
       EMPTY STATE
    ═══════════════════════════════════════════ */
    .blog-empty {
      grid-column: 1 / -1;
      text-align: center;
      padding: 70px 20px;
    }
    .blog-empty__icon {
      width: 80px; height: 80px;
      background: rgba(47,73,209,.08);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .blog-empty__icon svg { width: 36px; height: 36px; color: #2f49d1; opacity: .45; }
    .blog-empty h3 { font-size: 16px; color: #171717; margin-bottom: 6px; font-weight: 600; }
    .blog-empty p  { font-size: 13px; color: #b9b9b9; line-height: 1.6; }

    /* ═══════════════════════════════════════════
       PAGINATION
    ═══════════════════════════════════════════ */
    .blog-pagination {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 36px;
      flex-wrap: wrap;
    }
    .blog-pagination a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 38px;
      height: 38px;
      padding: 0 12px;
      border-radius: 10px;
      font-size: 13px;
      font-weight: 600;
      color: #767676;
      background: #fff;
      border: 1.5px solid #EEEEEE;
      text-decoration: none;
      transition: all .2s;
    }
    .blog-pagination a:hover { border-color: #2f49d1; color: #2f49d1; }
    .blog-pagination a.active {
      background: #2f49d1;
      color: #fff;
      border-color: #2f49d1;
      box-shadow: 0 4px 14px rgba(47,73,209,.3);
    }
    .blog-pagination a.disabled { opacity: .35; pointer-events: none; }

    /* ═══════════════════════════════════════════
       MODAL DE CONFIRMATION SUPPRESSION
    ═══════════════════════════════════════════ */
    .modal-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.45);
      backdrop-filter: blur(4px);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
      background: #fff;
      border-radius: 18px;
      padding: 32px 28px;
      max-width: 420px;
      width: 100%;
      text-align: center;
      box-shadow: 0 24px 64px rgba(0,0,0,.18);
      animation: modalIn .25s ease;
    }
    @keyframes modalIn {
      from { transform: scale(.92); opacity: 0; }
      to   { transform: scale(1);   opacity: 1; }
    }
    .modal-icon {
      width: 60px; height: 60px;
      border-radius: 50%;
      background: rgba(242,100,100,.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .modal-icon svg { width: 28px; height: 28px; color: #e04b4b; }
    .modal-box h3  { font-size: 18px; font-weight: 700; color: #171717; margin-bottom: 8px; }
    .modal-box p   { font-size: 13.5px; color: #767676; line-height: 1.6; margin-bottom: 24px; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-cancel  {
      flex: 1; padding: 11px; border-radius: 9px; border: 1.5px solid #EEEEEE;
      background: #fff; font-size: 14px; font-weight: 600; color: #767676; cursor: pointer;
      transition: all .2s;
    }
    .modal-cancel:hover  { border-color: #b9b9b9; color: #171717; }
    .modal-confirm {
      flex: 1; padding: 11px; border-radius: 9px; border: none;
      background: #e04b4b; font-size: 14px; font-weight: 700; color: #fff; cursor: pointer;
      transition: all .2s;
    }
    .modal-confirm:hover { background: #c93c3c; }

    /* ═══════════════════════════════════════════
       DARK MODE
    ═══════════════════════════════════════════ */
    .darkmode .blog-card             { background: #222235; border-color: #2c2c42; }
    .darkmode .blog-card--draft      { border-color: #4a3a1a; }
    .darkmode .blog-card__title      { color: #EFF0F6; }
    .darkmode .blog-card__footer     { border-color: #2c2c42; }
    .darkmode .blog-card__author-name{ color: #EFF0F6; }
    .darkmode .filter-chip           { background: #222235; border-color: #37374F; color: #D6D7E3; }
    .darkmode .filter-chip.active    { background: #2f49d1; border-color: #2f49d1; color: #fff; }
    .darkmode .blog-pagination a     { background: #222235; border-color: #37374F; color: #D6D7E3; }
    .darkmode .blog-empty__icon      { background: rgba(47,73,209,.15); }
    .darkmode .blog-empty h3         { color: #EFF0F6; }
    .darkmode .statut-tabs           { background: #1a1a2e; }
    .darkmode .statut-tab.active     { background: #222235; color: #EFF0F6; }
    .darkmode .modal-box             { background: #222235; }
    .darkmode .modal-box h3          { color: #EFF0F6; }
    .darkmode .modal-cancel          { background: #2c2c42; border-color: #37374F; color: #D6D7E3; }
    .darkmode .btn-action--edit      { background: rgba(47,73,209,.15); }
    .darkmode .btn-action--delete    { background: rgba(242,100,100,.12); }
  </style>
</head>

<body>
  <div class="layer"></div>
  <a class="skip-link sr-only" href="#skip-target">Passer au contenu</a>

  <div class="page-flex">

    <?php include "./inside/aside.php"; ?>

    <div class="main-wrapper">

      <?php include "./inside/nav.php"; ?>

      <main class="main users" id="skip-target">
        <div class="container">

          <!-- ══════ HERO ══════ -->
          <div class="blog-hero">
            <div class="blog-hero__text">
              <h1> Gestion du Blog</h1>
              <p>Créez, modifiez et gérez tous vos articles. Les brouillons ne sont visibles que par vous.</p>
            </div>
            <div class="blog-hero__actions">
              <a href="add-blog.php" class="btn-new-article">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvel article
              </a>
              <div class="blog-hero__stats">
                <div class="blog-hero__stat">
                  <strong><?= $totalAll ?></strong>
                  <span>Total</span>
                </div>
                <div class="blog-hero__stat">
                  <strong><?= $stats['publié'] ?></strong>
                  <span>Publiés</span>
                </div>
                <div class="blog-hero__stat">
                  <strong><?= $stats['brouillon'] ?></strong>
                  <span>Brouillons</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ══════ FLASH MESSAGES ══════ -->
          <?php if ($flashSuccess): ?>
            <div class="alert alert-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <?= htmlspecialchars($flashSuccess) ?>
            </div>
          <?php endif; ?>
          <?php if ($flashError): ?>
            <div class="alert alert-danger">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?= htmlspecialchars($flashError) ?>
            </div>
          <?php endif; ?>

          <!-- ══════ TOOLBAR ══════ -->
          <div class="blog-toolbar">
            <!-- Onglets statut -->
            <div class="statut-tabs">
              <?php
              $q0 = $catFilter ? '&cat=' . urlencode($catFilter) : '';
              ?>
              <a href="?page=1<?= $q0 ?>"
                 class="statut-tab <?= $statutFilter === '' ? 'active' : '' ?>">
                Tous <span class="tab-count"><?= $totalAll ?></span>
              </a>
              <a href="?page=1&statut=publi%C3%A9<?= $q0 ?>"
                 class="statut-tab <?= $statutFilter === 'publié' ? 'active' : '' ?>">
                 Publiés <span class="tab-count"><?= $stats['publié'] ?></span>
              </a>
              <a href="?page=1&statut=brouillon<?= $q0 ?>"
                 class="statut-tab <?= $statutFilter === 'brouillon' ? 'active' : '' ?>">
                 Brouillons <span class="tab-count"><?= $stats['brouillon'] ?></span>
              </a>
            </div>

            <!-- Filtre catégories -->
            <?php if (!empty($categories)): ?>
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
              <?php $qs = $statutFilter ? '&statut=' . urlencode($statutFilter) : ''; ?>
              <a href="?page=1<?= $qs ?>"
                 class="filter-chip <?= $catFilter === '' ? 'active' : '' ?>">Toutes</a>
              <?php foreach ($categories as $cat): ?>
                <a href="?page=1&cat=<?= urlencode($cat) . $qs ?>"
                   class="filter-chip <?= $catFilter === $cat ? 'active' : '' ?>">
                  <?= htmlspecialchars($cat) ?>
                </a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <!-- ══════ GRILLE ══════ -->
          <div class="blog-grid">

            <?php if (empty($blogs)): ?>

              <div class="blog-empty">
                <div class="blog-empty__icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0
                         01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <h3>Aucun article trouvé</h3>
                <p>Aucun article<?= $catFilter ? ' dans cette catégorie' : '' ?><?= $statutFilter ? ' avec ce statut' : '' ?> pour le moment.</p>
              </div>

            <?php else: ?>

              <?php $isFirst = true; ?>
              <?php foreach ($blogs as $blog):
                $initiale  = strtoupper(mb_substr(trim($blog['auteur']), 0, 1));
                $dateF     = date('d M Y', strtotime($blog['created_at']));
                $imgSrc    = !empty($blog['photo_couverture'])
                             ? '../../' . $blog['photo_couverture']
                             : null;
                $isDraft   = $blog['statut'] !== 'publié';
              ?>

                <article class="blog-card <?= $isFirst ? 'blog-card--featured' : '' ?> <?= $isDraft ? 'blog-card--draft' : '' ?>">

                  <!-- Vignette -->
                  <div class="blog-card__thumb">
                    <?php if ($imgSrc): ?>
                      <img src="<?= htmlspecialchars($imgSrc) ?>"
                           alt="<?= htmlspecialchars($blog['titre']) ?>"
                           loading="<?= $isFirst ? 'eager' : 'lazy' ?>">
                    <?php else: ?>
                      <div class="blog-card__no-img">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586
                               a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2
                               0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                      </div>
                    <?php endif; ?>

                    <?php if (!empty($blog['categorie'])): ?>
                      <span class="blog-card__cat">
                        <?= htmlspecialchars($blog['categorie']) ?>
                      </span>
                    <?php endif; ?>

                    <!-- Badge statut -->
                    <span class="blog-card__status <?= $isDraft ? 'blog-card__status--draft' : 'blog-card__status--published' ?>">
                      <span class="blog-card__status-dot"></span>
                      <?= $isDraft ? 'Brouillon' : 'Publié' ?>
                    </span>

                    <span class="blog-card__date-badge"><?= $dateF ?></span>
                  </div>

                  <!-- Corps -->
                  <div class="blog-card__body">
                    <h3 class="blog-card__title">
                      <?= htmlspecialchars($blog['titre']) ?>
                    </h3>

                    <?php if (!empty($blog['intro'])): ?>
                      <p class="blog-card__intro">
                        <?= htmlspecialchars($blog['intro']) ?>
                      </p>
                    <?php endif; ?>

                    <div class="blog-card__footer">
                      <div class="blog-card__author">
                        <div class="blog-card__avatar"><?= $initiale ?></div>
                        <span class="blog-card__author-name">
                          <?= htmlspecialchars($blog['auteur']) ?>
                        </span>
                      </div>

                      <!-- ── Boutons Edit / Delete ── -->
                      <div class="blog-card__actions">
                        <a href="edit-blog.php?id=<?= (int)$blog['id_blog'] ?>"
                           class="btn-action btn-action--edit"
                           title="Modifier cet article">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </a>
                        <button type="button"
                                class="btn-action btn-action--delete"
                                title="Supprimer cet article"
                                data-id="<?= (int)$blog['id_blog'] ?>"
                                data-titre="<?= htmlspecialchars($blog['titre'], ENT_QUOTES) ?>"
                                onclick="openDeleteModal(this)">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>

                </article>

                <?php $isFirst = false; ?>
              <?php endforeach; ?>

            <?php endif; ?>

          </div><!-- /blog-grid -->

          <!-- ══════ PAGINATION ══════ -->
          <?php if ($totalPages > 1): ?>
            <?php
            $qParts = [];
            if ($catFilter)    $qParts[] = 'cat='    . urlencode($catFilter);
            if ($statutFilter) $qParts[] = 'statut=' . urlencode($statutFilter);
            $q = $qParts ? '&' . implode('&', $qParts) : '';
            ?>
            <nav class="blog-pagination" aria-label="Pagination">
              <a href="?page=<?= $page - 1 . $q ?>"
                 class="<?= $page <= 1 ? 'disabled' : '' ?>" aria-label="Précédent">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
              </a>
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i . $q ?>" class="<?= $i === $page ? 'active' : '' ?>">
                  <?= $i ?>
                </a>
              <?php endfor; ?>
              <a href="?page=<?= $page + 1 . $q ?>"
                 class="<?= $page >= $totalPages ? 'disabled' : '' ?>" aria-label="Suivant">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
              </a>
            </nav>
          <?php endif; ?>

        </div><!-- /container -->
      </main>

      <?php include "./inside/footer.php"; ?>
    </div>
  </div>

  <!-- ══════ MODAL SUPPRESSION ══════ -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
          <path d="M10 11v6M14 11v6"/>
          <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
        </svg>
      </div>
      <h3>Supprimer l'article ?</h3>
      <p id="deleteModalText">Cette action est irréversible. L'article sera définitivement supprimé.</p>
      <div class="modal-actions">
        <button class="modal-cancel" onclick="closeDeleteModal()">Annuler</button>
        <form method="POST" action="model/blog-crud.php" id="deleteForm" style="flex:1;">
          <input type="hidden" name="id_blog" id="deleteIdInput">
          <button type="submit" name="delete_blog" class="modal-confirm" style="width:100%;">Supprimer</button>
        </form>
      </div>
    </div>
  </div>

  <script src="../assets/plugins/chart.min.js"></script>
  <script src="../assets/plugins/feather.min.js"></script>
  <script src="../assets/js/script.js"></script>
  <script>
    function openDeleteModal(btn) {
      document.getElementById('deleteIdInput').value  = btn.dataset.id;
      document.getElementById('deleteModalText').textContent =
        '« ' + btn.dataset.titre + ' » sera définitivement supprimé. Cette action est irréversible.';
      document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('open');
    }
    // Fermer en cliquant en dehors
    document.getElementById('deleteModal').addEventListener('click', function(e) {
      if (e.target === this) closeDeleteModal();
    });
    // Fermer avec Escape
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
  </script>
</body>
</html>
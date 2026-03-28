<?php
require_once "./model/post-crud.php";
require_once "./model/post-video-crud.php";

// ── Pagination ────────────────────────────────────────────────────────────────
$per_page = 6;

$page_articles = max(1, (int) ($_GET['page_a'] ?? 1));
$page_videos   = max(1, (int) ($_GET['page_v'] ?? 1));

$offset_articles = ($page_articles - 1) * $per_page;
$offset_videos   = ($page_videos   - 1) * $per_page;

// ── Récupération des données ──────────────────────────────────────────────────
function countRows(PDO $db, string $table): int {
    return (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$total_articles  = countRows($conDB, 'articles');
$total_videos    = countRows($conDB, 'articles_video');

$articles        = getAllArticles($conDB, $per_page, $offset_articles);
$videos          = getAllVideos($conDB,   $per_page, $offset_videos);

$total_pages_a   = (int) ceil($total_articles / $per_page);
$total_pages_v   = (int) ceil($total_videos   / $per_page);

// ── Fonction utilitaire : pagination URL ──────────────────────────────────────
function paginationUrl(string $param, int $page): string {
    $params = $_GET;
    $params[$param] = $page;
    return '?' . http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Publications</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
    /* ── Tabs ── */
    .pub-tabs {
      display: flex;
      gap: 4px;
      background: #eff0f6;
      border-radius: 10px;
      padding: 4px;
      width: fit-content;
      margin-bottom: 28px;
    }
    .pub-tab {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      background: transparent;
      color: #767676;
      transition: 0.2s all;
      text-decoration: none;
    }
    .pub-tab.active {
      background: #fff;
      color: #2f49d1;
      box-shadow: 0 2px 8px rgba(47,73,209,0.10);
    }
    .pub-tab svg { width: 16px; height: 16px; }
    .pub-tab .tab-badge {
      font-size: 11px;
      font-weight: 700;
      padding: 1px 7px;
      border-radius: 20px;
      background: rgba(47,73,209,0.1);
      color: #2f49d1;
    }
    .pub-tab.active .tab-badge {
      background: rgba(47,73,209,0.12);
    }
    .pub-tab.tab-video .tab-badge {
      background: rgba(242,100,100,0.1);
      color: #f26464;
    }

    /* ── Section title bar ── */
    .section-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 20px;
    }
    .section-bar__left {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-bar__icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .section-bar__icon.blue  { background: rgba(47,73,209,0.1);  color: #2f49d1; }
    .section-bar__icon.red   { background: rgba(242,100,100,0.1); color: #f26464; }
    .section-bar__title {
      font-weight: 700;
      font-size: 17px;
      color: #171717;
    }
    .section-bar__count {
      font-size: 12px;
      color: #b9b9b9;
      font-weight: 500;
    }

    /* ── Cards grille ── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    @media (max-width: 991px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 575px) { .cards-grid { grid-template-columns: 1fr; } }

    /* ── Article card ── */
    .article-card {
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 2px 12px rgba(160,163,189,0.10);
      transition: 0.25s all;
      position: relative;
    }
    .article-card:hover {
      box-shadow: 0 6px 24px rgba(47,73,209,0.13);
      transform: translateY(-2px);
    }
    .article-card__img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      display: block;
      background: #eff0f6;
    }
    .article-card__img-placeholder {
      width: 100%;
      height: 160px;
      background: linear-gradient(135deg, #eff0f6 0%, #e0e2f0 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #d6d7e3;
    }
    .article-card__body {
      padding: 14px 16px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .article-card__author {
      font-size: 11px;
      font-weight: 600;
      color: #2f49d1;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .article-card__desc {
      font-size: 13px;
      color: #767676;
      line-height: 1.55;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
    }
    .article-card__date {
      font-size: 11px;
      color: #b9b9b9;
      display: flex;
      align-items: center;
      gap: 5px;
      margin-top: 4px;
    }
    .article-card__footer {
      padding: 10px 16px;
      border-top: 1px solid #eeeeee;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
    .article-card__link {
      font-size: 12px;
      font-weight: 500;
      color: #5887ff;
      display: flex;
      align-items: center;
      gap: 4px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 120px;
    }
    .article-card__actions {
      display: flex;
      gap: 6px;
      flex-shrink: 0;
    }
    .action-btn {
      width: 30px;
      height: 30px;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: 0.2s all;
      padding: 0;
    }
    .action-btn.edit   { background: rgba(47,73,209,0.08);  color: #2f49d1; }
    .action-btn.delete { background: rgba(242,100,100,0.08); color: #f26464; }
    .action-btn:hover  { filter: brightness(0.9); }
    .action-btn svg    { width: 14px; height: 14px; }

    /* ── Video card ── */
    .video-card {
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      box-shadow: 0 2px 12px rgba(160,163,189,0.10);
      transition: 0.25s all;
    }
    .video-card:hover {
      box-shadow: 0 6px 24px rgba(242,100,100,0.12);
      transform: translateY(-2px);
    }
    .video-card__thumb {
      position: relative;
      width: 100%;
      aspect-ratio: 16/9;
      overflow: hidden;
      background: #111;
    }
    .video-card__thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: 0.3s transform;
    }
    .video-card:hover .video-card__thumb img { transform: scale(1.04); }
    .video-card__play {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(0,0,0,0.3);
      transition: 0.2s all;
    }
    .video-card:hover .video-card__play { background: rgba(0,0,0,0.45); }
    .video-card__play-btn {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background: rgba(242,100,100,0.92);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 16px rgba(0,0,0,0.35);
    }
    .video-card__play-btn svg { color: #fff; width: 20px; height: 20px; margin-left: 3px; }
    .video-card__body {
      padding: 12px 14px;
      flex: 1;
    }
    .video-card__title {
      font-weight: 600;
      font-size: 14px;
      color: #171717;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      margin-bottom: 6px;
    }
    .video-card__date {
      font-size: 11px;
      color: #b9b9b9;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .video-card__footer {
      padding: 10px 14px;
      border-top: 1px solid #eeeeee;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .video-card__yt-badge {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 600;
      color: #f26464;
    }

    /* ── Empty state ── */
    .empty-state {
      grid-column: 1 / -1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
      color: #b9b9b9;
      gap: 12px;
      text-align: center;
    }
    .empty-state svg { width: 48px; height: 48px; opacity: 0.4; }
    .empty-state p   { font-size: 14px; font-weight: 500; }

    /* ── Pagination ── */
    .pagination-wrapper {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 4px;
    }
    .pagination-info {
      font-size: 13px;
      color: #b9b9b9;
      font-weight: 500;
    }
    .pagination {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .pagination a, .pagination span {
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 34px;
      height: 34px;
      padding: 0 8px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      transition: 0.2s all;
      color: #767676;
      background: #fff;
      border: 1px solid #eeeeee;
    }
    .pagination a:hover { background: #eff0f6; color: #2f49d1; border-color: #d6d7e3; }
    .pagination a.active, .pagination span.active {
      background: #2f49d1;
      color: #fff;
      border-color: #2f49d1;
    }
    .pagination span.dots {
      border: none;
      background: transparent;
      color: #b9b9b9;
    }
    .pagination a.disabled {
      opacity: 0.35;
      pointer-events: none;
    }

    /* ── Breadcrumb ── */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #b9b9b9;
      margin-bottom: 20px;
    }
    .breadcrumb a  { color: #2f49d1; }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* ── Section separator ── */
    .section-sep {
      border: none;
      border-top: 1px solid #eeeeee;
      margin: 36px 0 32px;
    }

    /* ── Dark mode ── */
    .darkmode .pub-tabs       { background: #222235; }
    .darkmode .pub-tab.active { background: #161624; color: #5887ff; box-shadow: none; }
    .darkmode .pub-tab        { color: #767676; }
    .darkmode .article-card,
    .darkmode .video-card     { background: #222235; box-shadow: none; }
    .darkmode .article-card__footer,
    .darkmode .video-card__footer { border-color: #37374B; }
    .darkmode .article-card__desc { color: #D6D7E3; }
    .darkmode .video-card__title  { color: #EFF0F6; }
    .darkmode .section-bar__title { color: #EFF0F6; }
    .darkmode .pagination a, .darkmode .pagination span {
      background: #222235;
      border-color: #37374B;
      color: #D6D7E3;
    }
    .darkmode .pagination a.active { background: #2f49d1; border-color: #2f49d1; color: #fff; }
    .darkmode .section-sep    { border-color: #37374B; }
    .darkmode .article-card__img-placeholder { background: #2a2a3e; }
  </style>
</head>

<body>
  <div class="layer"></div>
  <a class="skip-link sr-only" href="#skip-target">Skip to content</a>

  <div class="page-flex">
    <?php include "./inside/aside.php"; ?>

    <div class="main-wrapper">
      <?php include "./inside/nav.php"; ?>

      <main class="main users" id="skip-target">
        <div class="container">

          <!-- Breadcrumb -->
          <nav class="breadcrumb">
            <a href="index.php">Accueil</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
            <span>Publications</span>
          </nav>

          <!-- Title + bouton ajouter -->
          <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <h2 class="main-title" style="margin-bottom:0;">Publications</h2>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
              <a href="add_article.php" class="primary-default-btn" style="font-size:14px; padding:9px 16px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Article
              </a>
              <a href="add-post-video.php" class="secondary-default-btn" style="font-size:14px; padding:9px 16px; border-color:#f26464; color:#f26464;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="currentColor" style="margin-right:6px;">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
                Vidéo
              </a>
            </div>
          </div>

          <!-- ════════════════════════════════════════════════
               SECTION ARTICLES
          ════════════════════════════════════════════════ -->
          <div class="section-bar">
            <div class="section-bar__left">
              <div class="section-bar__icon blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
              </div>
              <div>
                <div class="section-bar__title">Articles</div>
                <div class="section-bar__count"><?= $total_articles ?> publication<?= $total_articles > 1 ? 's' : '' ?></div>
              </div>
            </div>
            <?php if ($total_articles > 0): ?>
              <div class="pagination-info">
                Page <?= $page_articles ?> / <?= max(1, $total_pages_a) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Grille articles -->
          <div class="cards-grid">
            <?php if (empty($articles)): ?>
              <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5">
                  <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
                <p>Aucun article publié pour le moment.</p>
                <a href="add_article.php" class="primary-default-btn" style="font-size:13px; padding:8px 16px;">
                  Ajouter un article
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($articles as $a): ?>
                <div class="article-card">

                  <?php if (!empty($a['photo'])): ?>
                    <img class="article-card__img"
                         src="../../<?= htmlspecialchars($a['photo']) ?>" alt="<?= htmlspecialchars($a['author']) ?>">
                  <?php else: ?>
                    <div class="article-card__img-placeholder">
                      <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <circle cx="8.5" cy="8.5" r="1.5"/>
                        <polyline points="21 15 16 10 5 21"/>
                      </svg>
                    </div>
                  <?php endif; ?>

                  <div class="article-card__body">
                    <div class="article-card__author">
                      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                      </svg>
                      <?= htmlspecialchars($a['author']) ?>
                    </div>
                    <p class="article-card__desc"><?= htmlspecialchars($a['description']) ?></p>
                    <div class="article-card__date">
                      <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                      </svg>
                      <?= !empty($a['created_at']) ? date('d M Y', strtotime($a['created_at'])) : '—' ?>
                    </div>
                  </div>

                  <div class="article-card__footer">
                    <?php if (!empty($a['link_article'])): ?>
                      <a href="<?= htmlspecialchars($a['link_article']) ?>"
                         target="_blank" rel="noopener"
                         class="article-card__link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                          <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Voir le lien
                      </a>
                    <?php else: ?>
                      <span></span>
                    <?php endif; ?>

                    <div class="article-card__actions">
                      <a href="edit_article.php?id=<?= $a['id_article'] ?>" class="action-btn edit" title="Modifier">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                      </a>
                      <form method="POST" action="./model/post-crud.php"
                            onsubmit="return confirm('Supprimer cet article ?');" style="margin:0;">
                        <input type="hidden" name="id_article" value="<?= $a['id_article'] ?>">
                        <button type="submit" name="delete_article" class="action-btn delete" title="Supprimer">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6"/><path d="M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>

                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Pagination articles -->
          <?php if ($total_pages_a > 1): ?>
            <div class="pagination-wrapper" style="margin-bottom: 8px;">
              <span class="pagination-info">
                <?= min($offset_articles + 1, $total_articles) ?>–<?= min($offset_articles + $per_page, $total_articles) ?>
                sur <?= $total_articles ?> articles
              </span>
              <nav class="pagination">
                <a href="<?= paginationUrl('page_a', $page_articles - 1) ?>"
                   class="<?= $page_articles <= 1 ? 'disabled' : '' ?>" title="Précédent">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                       fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="15 18 9 12 15 6"/>
                  </svg>
                </a>

                <?php for ($i = 1; $i <= $total_pages_a; $i++):
                  if ($i === 1 || $i === $total_pages_a || abs($i - $page_articles) <= 1): ?>
                    <a href="<?= paginationUrl('page_a', $i) ?>"
                       class="<?= $i === $page_articles ? 'active' : '' ?>">
                      <?= $i ?>
                    </a>
                  <?php elseif (abs($i - $page_articles) === 2): ?>
                    <span class="dots">…</span>
                  <?php endif;
                endfor; ?>

                <a href="<?= paginationUrl('page_a', $page_articles + 1) ?>"
                   class="<?= $page_articles >= $total_pages_a ? 'disabled' : '' ?>" title="Suivant">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                       fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"/>
                  </svg>
                </a>
              </nav>
            </div>
          <?php endif; ?>

          <hr class="section-sep">

          <!-- ════════════════════════════════════════════════
               SECTION VIDÉOS
          ════════════════════════════════════════════════ -->
          <div class="section-bar">
            <div class="section-bar__left">
              <div class="section-bar__icon red">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="currentColor">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
              </div>
              <div>
                <div class="section-bar__title">Vidéos YouTube</div>
                <div class="section-bar__count"><?= $total_videos ?> vidéo<?= $total_videos > 1 ? 's' : '' ?></div>
              </div>
            </div>
            <?php if ($total_videos > 0): ?>
              <div class="pagination-info">
                Page <?= $page_videos ?> / <?= max(1, $total_pages_v) ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Grille vidéos -->
          <div class="cards-grid">
            <?php if (empty($videos)): ?>
              <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="opacity:0.25; color:#f26464;">
                  <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                </svg>
                <p>Aucune vidéo ajoutée pour le moment.</p>
                <a href="add_video.php" class="secondary-default-btn"
                   style="font-size:13px; padding:8px 16px; border-color:#f26464; color:#f26464;">
                  Ajouter une vidéo
                </a>
              </div>
            <?php else: ?>
              <?php foreach ($videos as $v):
                // Extraire l'ID YouTube pour la miniature
                preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
                  $v['link_youtube'], $ytm);
                $yt_id = $ytm[1] ?? null;
                $thumb = $yt_id ? "https://img.youtube.com/vi/{$yt_id}/hqdefault.jpg" : null;
              ?>
                <div class="video-card">

                  <div class="video-card__thumb">
                    <?php if ($thumb): ?>
                      <img src="<?= htmlspecialchars($thumb) ?>"
                           alt="<?= htmlspecialchars($v['title_video']) ?>">
                    <?php else: ?>
                      <div style="width:100%;height:100%;background:#222;display:flex;align-items:center;justify-content:center;aspect-ratio:16/9;"></div>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($v['link_youtube']) ?>"
                       target="_blank" rel="noopener"
                       class="video-card__play">
                      <div class="video-card__play-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                          <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                      </div>
                    </a>
                  </div>

                  <div class="video-card__body">
                    <div class="video-card__title"><?= htmlspecialchars($v['title_video']) ?></div>
                    <div class="video-card__date">
                      <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                      </svg>
                      <?= !empty($v['created_at']) ? date('d M Y', strtotime($v['created_at'])) : '—' ?>
                    </div>
                  </div>

                  <div class="video-card__footer">
                    <div class="video-card__yt-badge">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                           fill="currentColor">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                      </svg>
                      YouTube
                    </div>
                    <div class="article-card__actions">
                      <a href="edit_video.php?id=<?= $v['id_video'] ?>" class="action-btn edit" title="Modifier">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                      </a>
                      <form method="POST" action="./model/video-crud.php"
                            onsubmit="return confirm('Supprimer cette vidéo ?');" style="margin:0;">
                        <input type="hidden" name="id_video" value="<?= $v['id_video'] ?>">
                        <button type="submit" name="delete_video" class="action-btn delete" title="Supprimer">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            <path d="M10 11v6"/><path d="M14 11v6"/>
                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>

                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Pagination vidéos -->
          <?php if ($total_pages_v > 1): ?>
            <div class="pagination-wrapper">
              <span class="pagination-info">
                <?= min($offset_videos + 1, $total_videos) ?>–<?= min($offset_videos + $per_page, $total_videos) ?>
                sur <?= $total_videos ?> vidéos
              </span>
              <nav class="pagination">
                <a href="<?= paginationUrl('page_v', $page_videos - 1) ?>"
                   class="<?= $page_videos <= 1 ? 'disabled' : '' ?>" title="Précédent">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                       fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="15 18 9 12 15 6"/>
                  </svg>
                </a>

                <?php for ($i = 1; $i <= $total_pages_v; $i++):
                  if ($i === 1 || $i === $total_pages_v || abs($i - $page_videos) <= 1): ?>
                    <a href="<?= paginationUrl('page_v', $i) ?>"
                       class="<?= $i === $page_videos ? 'active' : '' ?>">
                      <?= $i ?>
                    </a>
                  <?php elseif (abs($i - $page_videos) === 2): ?>
                    <span class="dots">…</span>
                  <?php endif;
                endfor; ?>

                <a href="<?= paginationUrl('page_v', $page_videos + 1) ?>"
                   class="<?= $page_videos >= $total_pages_v ? 'disabled' : '' ?>" title="Suivant">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                       fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"/>
                  </svg>
                </a>
              </nav>
            </div>
          <?php endif; ?>

        </div><!-- .container -->
      </main>

      <?php include "./inside/footer.php"; ?>
    </div><!-- .main-wrapper -->
  </div><!-- .page-flex -->
<!-- Chart library -->
<script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
</body>
</html>
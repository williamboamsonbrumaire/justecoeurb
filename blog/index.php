<?php
require_once __DIR__ . '/../manage/dashboard/model/blog-crud.php'; // ← intègre config + Connexion() + getAllBlogs()

// ── Fonction slug ─────────────────────────────────────────────────────────────
function makeSlug(string $str): string {
    $str = mb_strtolower(trim($str));
    $map = [
        'à'=>'a','â'=>'a','ä'=>'a','á'=>'a','ã'=>'a',
        'è'=>'e','é'=>'e','ê'=>'e','ë'=>'e',
        'î'=>'i','ï'=>'i','í'=>'i','ì'=>'i',
        'ô'=>'o','ö'=>'o','ó'=>'o','ò'=>'o','õ'=>'o',
        'ù'=>'u','û'=>'u','ü'=>'u','ú'=>'u',
        'ç'=>'c','ñ'=>'n','œ'=>'oe','æ'=>'ae',
    ];
    $str = strtr($str, $map);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

// ── Pagination ────────────────────────────────────────────────────────────────
$limit  = 9;
$page   = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// ── Filtre catégorie ──────────────────────────────────────────────────────────
$catFilter = trim($_GET['cat'] ?? '');

// ── getAllBlogs() du modèle — publiés uniquement pour le front public ──────────
$blogs = getAllBlogs($conDB, $limit, $offset, 'publié');

// Filtre catégorie côté PHP
if ($catFilter !== '') {
    $blogs = array_values(array_filter($blogs, fn($b) => $b['categorie'] === $catFilter));
}

// ── Total pour pagination ─────────────────────────────────────────────────────
try {
    $stmtCount  = $conDB->query("SELECT COUNT(*) FROM blog WHERE statut = 'publié'");
    $totalBlogs = (int)$stmtCount->fetchColumn();
} catch (Exception $e) { $totalBlogs = 0; }
$totalPages = (int)ceil($totalBlogs / $limit);

// ── Catégories sidebar ────────────────────────────────────────────────────────
try {
    $stmtCat    = $conDB->query("SELECT DISTINCT categorie FROM blog WHERE statut='publié' AND categorie IS NOT NULL ORDER BY categorie");
    $categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $categories = []; }

// ── Articles récents sidebar ──────────────────────────────────────────────────
try {
    $stmtRecent  = $conDB->query("SELECT id_blog, titre, photo_couverture, created_at FROM blog WHERE statut='publié' ORDER BY created_at DESC LIMIT 4");
    $recentPosts = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $recentPosts = []; }

// ── Tags dynamiques sidebar ───────────────────────────────────────────────────
try {
    $stmtTags   = $conDB->query("SELECT tags FROM blog WHERE statut='publié' AND tags IS NOT NULL");
    $allTagsRaw = $stmtTags->fetchAll(PDO::FETCH_COLUMN);
    $allTags    = [];
    foreach ($allTagsRaw as $tagStr) {
        foreach (array_map('trim', explode(',', $tagStr)) as $t) {
            if ($t !== '') $allTags[$t] = ($allTags[$t] ?? 0) + 1;
        }
    }
    arsort($allTags);
    $allTags = array_slice($allTags, 0, 12, true);
} catch (Exception $e) { $allTags = []; }
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta property="og:title"       content="Blog | Juste-Cœur Beaubrun" />
  <meta property="og:description" content="Chroniques, analyses et réflexions de Juste-Cœur Beaubrun." />
  <meta property="og:url"         content="https://justecoeurb.ht/blog" />
  <meta property="og:image"       content="https://justecoeurb.ht/img/banner_bg_2.png" />
  <meta property="og:type"        content="website" />
  <meta name="twitter:card"  content="summary_large_image">
  <meta name="twitter:image" content="https://justecoeurb.ht/img/banner_bg_2.png">
  <title>Blog | Juste-Cœur Beaubrun</title>
  <link rel="icon" href="img/">
  <link rel="stylesheet" href="../public/css/bootstrap.min.css">
  <link rel="stylesheet" href="../public/css/animate.css">
  <link rel="stylesheet" href="../public/css/owl.carousel.min.css">
  <link rel="stylesheet" href="../public/css/themify-icons.css">
  <link rel="stylesheet" href="../public/css/flaticon.css">
  <link rel="stylesheet" href="../public/css/magnific-popup.css">
  <link rel="stylesheet" href="../public/css/nice-select.css">
  <link rel="stylesheet" href="../public/css/all.css">
  <link rel="stylesheet" href="../public/css/style.css">
  <style>
    /* ── Filtre catégories ── */
    .cat-filter-bar {
      display: flex; flex-wrap: wrap; gap: 8px;
      margin-bottom: 28px; padding-bottom: 20px;
      border-bottom: 1px solid #f0f0f0;
    }
    .cat-chip {
      display: inline-flex; align-items: center;
      padding: 5px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;
      border: 1.5px solid #e0e0e0; background: #fff; color: #777;
      text-decoration: none; transition: all .2s;
    }
    .cat-chip:hover  { border-color: #2f49d1; color: #2f49d1; }
    .cat-chip.active { background: #2f49d1; border-color: #2f49d1; color: #fff !important; }

    /* ── Blog item ── */
    .blog_item { margin-bottom: 40px; border-bottom: 1px solid #f0f0f0; padding-bottom: 36px; }
    .blog_item:last-of-type { border-bottom: none; }

    .blog_item_img_wrap {
      position: relative; overflow: hidden; display: block;
      text-decoration: none;
    }
    .blog_item_img_wrap .card-img {
      width: 100%; height: 300px; object-fit: cover;
      transition: transform .45s ease; display: block; border-radius: 0;
    }
    .blog_item:hover .blog_item_img_wrap .card-img { transform: scale(1.04); }

    .blog_item_no_img {
      width: 100%; height: 300px;
      background: linear-gradient(135deg, #eff0f6 0%, #dde2f7 100%);
      display: flex; align-items: center; justify-content: center;
    }
    .blog_item_no_img svg { width: 64px; height: 64px; opacity: .12; color: #2f49d1; }

    /* Date overlay */
    .blog_item_date_badge {
      position: absolute; bottom: 0; left: 0;
      background: #2f49d1; color: #fff;
      text-align: center; padding: 10px 16px; line-height: 1; z-index: 1;
    }
    .blog_item_date_badge h3 { font-size: 22px; font-weight: 700; margin: 0; color: #fff; }
    .blog_item_date_badge p  { font-size: 11px; margin: 4px 0 0; color: rgba(255,255,255,.8); text-transform: uppercase; letter-spacing: .5px; }

    /* Catégorie overlay */
    .blog_item_cat_badge {
      position: absolute; top: 12px; right: 12px;
      background: #2f49d1; color: #fff;
      font-size: 10px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase;
      padding: 4px 12px; border-radius: 20px; z-index: 1;
    }

    /* ── Détails ── */
    .blog_details { padding-top: 20px; }
    .blog_details .blog_title {
      font-size: 20px; font-weight: 700; line-height: 1.45;
      margin-bottom: 12px; color: #171717;
      text-decoration: none; display: inline-block; transition: color .2s;
    }
    .blog_details .blog_title:hover { color: #2f49d1; }
    .blog_details p {
      font-size: 14px; line-height: 1.8; color: #777; margin-bottom: 16px;
      display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }

    /* ── Lire la suite ── */
    .btn_read_more {
      display: inline-flex; align-items: center; gap: 8px;
      background: #2f49d1; color: #fff !important;
      font-size: 13px; font-weight: 600; padding: 10px 22px;
      border-radius: 6px; text-decoration: none; transition: all .2s; margin-top: 6px;
    }
    .btn_read_more:hover { background: #1e35a8; transform: translateY(-1px); }
    .btn_read_more svg { width: 14px; height: 14px; transition: transform .2s; }
    .btn_read_more:hover svg { transform: translateX(3px); }

    /* ── Sidebar récents ── */
    .post_item {
      display: flex; gap: 14px; align-items: flex-start;
      margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f0f0f0;
    }
    .post_item:last-child { border-bottom: none; margin-bottom: 0; }
    .post_item img { width: 68px; height: 68px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
    .post_item .post_thumb_ph { width: 68px; height: 68px; border-radius: 6px; flex-shrink: 0; background: linear-gradient(135deg,#eff0f6,#dde2f7); }
    .post_item .media-body { padding-left: 0; }
    .post_item .media-body h3 { font-size: 13px; font-weight: 600; line-height: 1.4; margin-bottom: 4px; color: #171717; }
    .post_item .media-body h3 a { color: inherit; text-decoration: none; }
    .post_item .media-body h3 a:hover { color: #2f49d1; }
    .post_item .media-body p { font-size: 11px; color: #b9b9b9; margin: 0; }

    /* ── Empty state ── */
    .blog-empty { text-align: center; padding: 60px 20px; }
    .blog-empty svg { width: 64px; height: 64px; opacity: .15; color: #2f49d1; margin-bottom: 16px; }
    .blog-empty h3 { font-size: 18px; color: #555; margin-bottom: 8px; }
    .blog-empty p  { font-size: 14px; color: #999; }
  </style>
</head>

<body>
  <!--::header part start::-->
  <?php require_once "../includes/header.php"; ?>
  <!-- Header part end-->

  <!-- breadcrumb -->
  <section class="breadcrumb breadcrumb_bg">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="breadcrumb_iner">
            <div class="breadcrumb_iner_item">
              <h2>Chroniques et analyses issues de ses réflexions personnelles</h2>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!--================Blog Area =================-->
  <section class="blog_area section_padding">
    <div class="container">
      <div class="row">

        <!-- ══ GAUCHE ══ -->
        <div class="col-lg-8 mb-5 mb-lg-0">
          <div class="blog_left_sidebar">

            <!-- Filtres catégories -->
            <?php if (!empty($categories)): ?>
              <div class="cat-filter-bar">
                <a href="blog.php" class="cat-chip <?= $catFilter === '' ? 'active' : '' ?>">Tous</a>
                <?php foreach ($categories as $cat): ?>
                  <a href="blog.php?cat=<?= urlencode($cat) ?>"
                     class="cat-chip <?= $catFilter === $cat ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (empty($blogs)): ?>
              <div class="blog-empty">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414
                       5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3>Aucun article trouvé</h3>
                <p>Revenez bientôt, de nouveaux articles seront publiés prochainement.</p>
              </div>

            <?php else: ?>

              <?php foreach ($blogs as $blog):
                // ── Slug = titre transformé ──────────────────────────────────
                $slug   = makeSlug($blog['titre']);
                $url    = '/jcb/blog/' . $slug; // → redirige vers blog-detail.php via .htaccess

                $dateObj = new DateTime($blog['created_at']);
                $jour    = $dateObj->format('d');
                $mois    = $dateObj->format('M');

                $imgSrc = !empty($blog['photo_couverture'])
                          ? '../' . ltrim($blog['photo_couverture'], '/')
                          : null;

                $intro  = !empty($blog['intro'])
                          ? $blog['intro']
                          : mb_substr(strip_tags($blog['contenu'] ?? ''), 0, 220) . '…';
              ?>

                <article class="blog_item">

                  <!-- Image + badges overlay -->
                  <a href="<?= $url ?>" class="blog_item_img_wrap">
                    <?php if ($imgSrc): ?>
                      <img class="card-img rounded-0"
                           src="<?= htmlspecialchars($imgSrc) ?>"
                           alt="<?= htmlspecialchars($blog['titre']) ?>"
                           loading="lazy">
                    <?php else: ?>
                      <div class="blog_item_no_img">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828
                               0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                      </div>
                    <?php endif; ?>
                    <span class="blog_item_date_badge">
                      <h3><?= $jour ?></h3>
                      <p style="color: #f1f1f1 !important;" ><?= $mois ?></p>
                    </span>
                    <?php if (!empty($blog['categorie'])): ?>
                      <span class="blog_item_cat_badge"><?= htmlspecialchars($blog['categorie']) ?></span>
                    <?php endif; ?>
                  </a>

                  <!-- Corps -->
                  <div class="blog_details">
                    <a class="blog_title" href="<?= $url ?>">
                      <?= htmlspecialchars($blog['titre']) ?>
                    </a>

                    <p><?= htmlspecialchars($intro) ?></p>

                    <ul class="blog-info-link">
                      <li>
                        <a href="#">
                          <i class="far fa-user"></i>
                          <?= htmlspecialchars($blog['auteur'] ?? 'Juste-Cœur Beaubrun') ?>
                        </a>
                      </li>
                      <?php if (!empty($blog['categorie'])): ?>
                        <li>
                          <a href="blog.php?cat=<?= urlencode($blog['categorie']) ?>">
                            <i class="far fa-folder"></i>
                            <?= htmlspecialchars($blog['categorie']) ?>
                          </a>
                        </li>
                      <?php endif; ?>
                    </ul>

                    <!-- ══ LIRE LA SUITE → /blog/slug-du-titre ══ -->
                    <a style="color: #f1f1f1 !important;"   href="<?= $url ?>" class="btn_read_more">
                      Lire la suite
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                           stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                      </svg>
                    </a>
                  </div>

                </article>

              <?php endforeach; ?>

            <?php endif; ?>

            <!-- ── Pagination ── -->
            <?php if ($totalPages > 1): ?>
              <?php $qs = $catFilter ? '&cat=' . urlencode($catFilter) : ''; ?>
              <nav class="blog-pagination justify-content-center d-flex">
                <ul class="pagination">
                  <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a href="blog.php?page=<?= ($page - 1) . $qs ?>" class="page-link" aria-label="Précédent">
                      <i class="ti-angle-left"></i>
                    </a>
                  </li>
                  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                      <a href="blog.php?page=<?= $i . $qs ?>" class="page-link"><?= $i ?></a>
                    </li>
                  <?php endfor; ?>
                  <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a href="blog.php?page=<?= ($page + 1) . $qs ?>" class="page-link" aria-label="Suivant">
                      <i class="ti-angle-right"></i>
                    </a>
                  </li>
                </ul>
              </nav>
            <?php endif; ?>

          </div>
        </div>

        <!-- ══ SIDEBAR ══ -->
        <div class="col-lg-4">
          <div class="blog_right_sidebar">

          
            <!-- Catégories -->
            <?php if (!empty($categories)): ?>
              <aside class="single_sidebar_widget post_category_widget">
                <h4 class="widget_title">Catégories</h4>
                <ul class="list cat-list">
                  <?php foreach ($categories as $cat): ?>
                    <li>
                      <a href="blog.php?cat=<?= urlencode($cat) ?>" class="d-flex">
                        <p><?= htmlspecialchars($cat) ?></p>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </aside>
            <?php endif; ?>

            <!-- Articles récents -->
            <?php if (!empty($recentPosts)): ?>
              <aside class="single_sidebar_widget popular_post_widget">
                <h3 class="widget_title">Articles récents</h3>
                <?php foreach ($recentPosts as $rp):
                  $rSlug = makeSlug($rp['titre']);
                  $rDate = date('d M Y', strtotime($rp['created_at']));
                  $rImg  = !empty($rp['photo_couverture'])
                           ? '../' . ltrim($rp['photo_couverture'], '/')
                           : null;
                ?>
                  <div class="post_item media">
                    <?php if ($rImg): ?>
                      <img src="<?= htmlspecialchars($rImg) ?>"
                           alt="<?= htmlspecialchars($rp['titre']) ?>" loading="lazy">
                    <?php else: ?>
                      <div class="post_thumb_ph"></div>
                    <?php endif; ?>
                    <div class="media-body" style="padding-left:14px;">
                      <h3>
                        <a href="/blog/<?= $rSlug ?>">
                          <?= htmlspecialchars(mb_substr($rp['titre'], 0, 55)) ?><?= mb_strlen($rp['titre']) > 55 ? '…' : '' ?>
                        </a>
                      </h3>
                      <p><?= $rDate ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </aside>
            <?php endif; ?>

            <!-- Tags dynamiques -->
            <?php if (!empty($allTags)): ?>
              <aside class="single_sidebar_widget tag_cloud_widget">
                <h4 class="widget_title">Tags</h4>
                <ul class="list">
                  <?php foreach ($allTags as $tag => $count): ?>
                    <li>
                      <a href="blog.php?q=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </aside>
            <?php endif; ?>

            <!-- Newsletter -->
            <aside class="single_sidebar_widget newsletter_widget">
              <h4 class="widget_title">Newsletter</h4>
              <form action="#">
                <div class="form-group">
                  <input type="email" class="form-control"
                         onfocus="this.placeholder=''"
                         onblur="this.placeholder='Votre email'"
                         placeholder="Votre email" required>
                </div>
                <button class="button rounded-0 primary-bg text-white w-100 btn_1" type="submit">
                  S'abonner
                </button>
              </form>
            </aside>

          </div>
        </div>

      </div>
    </div>
  </section>
  <!--================Blog Area =================-->

  <section class="cta-section py-5" style="background-color: #0b1a3d; color: white;">
    <div class="container py-4">
        <div class="row align-items-center">

            <div class="col-lg-8 col-md-12 mb-4 mb-lg-0">
                <h2 class="display-6 text-white fw-bold mb-3">En savoir plus ou collaborer avec Juste-Cœur ?</h2>
                <p class="lead text-muted" style="color: #a8b0c0 !important;">
                    Vous pouvez explorer ses projets, ses publications ou entrer directement 
                    en contact pour imaginer de nouveaux programmes au service de la 
                    jeunesse et des territoires.
                </p>
            </div>

            <div class="col-lg-4 col-md-12 d-flex justify-content-lg-end justify-content-start flex-wrap gap-3">
                
                <a href="<?php echo base_url('projet'); ?>" class="btn btn-white mt-4 px-4 py-2">Découvrir ses engagements</a>
                
                <a href="<?php echo base_url('contact'); ?>" class="mt-3 btn btn-primary mt-4 px-4 py-2">Proposer une collaboration</a>
            </div>

        </div>
    </div>
</section>

<?php
// On remonte au dossier parent de "includes" pour trouver la racine, puis on pointe vers config.php
// Cela fonctionne que tu sois dans /index.php, /pages/about.php ou /blog/article.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/jcb/includes/config.php';
?>

<!-- ================== FOOTER ================== -->

<script src="<?php echo base_url('public/js/jquery-1.12.1.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/popper.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/jquery.magnific-popup.js'); ?>"></script>
<script src="<?php echo base_url('public/js/masonry.pkgd.js'); ?>"></script>
<script src="<?php echo base_url('public/js/owl.carousel.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/jquery.nice-select.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/custom.js'); ?>"></script>
<script src="<?php echo base_url('public/js/testimonial.js'); ?>"></script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Google Translate (invisible) -->
<div id="google_translate_element" style="display:none;"></div>
<script src="<?php echo base_url('public/js/global.js'); ?>"></script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> 


</body>
</html>
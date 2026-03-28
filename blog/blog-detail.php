<?php
/**
 * blog-detail.php
 * Affiché via l'URL propre : /blog/le-titre-du-blog
 * Le .htaccess traduit → blog-detail.php?slug=le-titre-du-blog
 */
require_once __DIR__ . '/../manage/dashboard/model/blog-crud.php';

// ── Fonction slug (identique à blog.php) ──────────────────────────────────────
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

// ── Récupération du slug ──────────────────────────────────────────────────────
$slug = trim($_GET['slug'] ?? '');

if ($slug === '') {
    header('Location: /jcb/blog');
    exit;
}

// ── Chercher l'article dont makeSlug(titre) === $slug ────────────────────────
$blog = null;
try {
    // On récupère tous les articles publiés et on compare le slug
    $stmt = $conDB->prepare(
        "SELECT * FROM blog WHERE statut = 'publié' ORDER BY created_at DESC"
    );
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        if (makeSlug($row['titre']) === $slug) {
            $blog = $row;
            break;
        }
    }
} catch (Exception $e) {
    // silence – $blog reste null → 404 ci-dessous
}

// ── 404 si non trouvé ─────────────────────────────────────────────────────────
if (!$blog) {
    http_response_code(404);
    // Optionnel : inclure une page 404 personnalisée
    // require_once '../includes/404.php'; exit;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Article introuvable | Blog</title>
  <link rel="stylesheet" href="../public/css/bootstrap.min.css">
  <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
  <?php require_once '../includes/header.php'; ?>
  <section class="blog_area section_padding">
    <div class="container text-center py-5">
      <h1 class="display-4">404</h1>
      <p class="lead">Cet article est introuvable ou a été supprimé.</p>
      <a href="/jcb/blog" class="btn btn-primary">← Retour au blog</a>
    </div>
  </section>
  <?php require_once '../includes/footer.php'; ?>
</body>
</html>
<?php
    exit;
}

// ── Tags de l'article ─────────────────────────────────────────────────────────
$tags = [];
if (!empty($blog['tags'])) {
    $tags = array_filter(array_map('trim', explode(',', $blog['tags'])));
}

// ── Articles récents sidebar ──────────────────────────────────────────────────
try {
    $stmtRecent  = $conDB->prepare(
        "SELECT id_blog, titre, photo_couverture, created_at
         FROM blog WHERE statut='publié' AND id_blog != :id
         ORDER BY created_at DESC LIMIT 4"
    );
    $stmtRecent->execute([':id' => $blog['id_blog']]);
    $recentPosts = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $recentPosts = []; }

// ── Catégories sidebar ────────────────────────────────────────────────────────
try {
    $stmtCat    = $conDB->query(
        "SELECT DISTINCT categorie FROM blog WHERE statut='publié' AND categorie IS NOT NULL ORDER BY categorie"
    );
    $categories = $stmtCat->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) { $categories = []; }

// ── Meta ──────────────────────────────────────────────────────────────────────
$metaTitle  = htmlspecialchars($blog['titre']) . ' | Blog Juste-Cœur Beaubrun';
$metaDesc   = !empty($blog['intro'])
              ? htmlspecialchars(mb_substr($blog['intro'], 0, 160))
              : htmlspecialchars(mb_substr(strip_tags($blog['contenu'] ?? ''), 0, 160));
$metaImg    = !empty($blog['photo_couverture'])
              ? 'https://justecoeurb.ht/' . ltrim($blog['photo_couverture'], '/')
              : 'https://justecoeurb.ht/img/banner_bg_2.png';

$canonicalSlug = makeSlug($blog['titre']);
$canonicalUrl  = 'https://justecoeurb.ht/blog/' . $canonicalSlug;

$dateObj = new DateTime($blog['created_at']);
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- SEO & OpenGraph -->
  <title><?= $metaTitle ?></title>
  <meta name="description" content="<?= $metaDesc ?>">
  <link rel="canonical" href="<?= $canonicalUrl ?>">

  <meta property="og:title"       content="<?= htmlspecialchars($blog['titre']) ?>">
  <meta property="og:description" content="<?= $metaDesc ?>">
  <meta property="og:url"         content="<?= $canonicalUrl ?>">
  <meta property="og:image"       content="<?= $metaImg ?>">
  <meta property="og:type"        content="article">
  <meta name="twitter:card"       content="summary_large_image">
  <meta name="twitter:image"      content="<?= $metaImg ?>">

  <!-- Feuilles de style (depuis blog/ → remonter d'un niveau) -->
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
    /* ── Image de couverture ── */
    .blog_detail_cover {
      width: 100%; max-height: 480px; object-fit: cover;
      border-radius: 8px; margin-bottom: 32px; display: block;
    }

    /* ── Contenu ── */
    .blog_detail_content h1 {
      font-size: 28px; font-weight: 800; line-height: 1.35;
      color: #171717; margin-bottom: 18px;
    }
    .blog_detail_meta {
      display: flex; flex-wrap: wrap; gap: 18px; align-items: center;
      margin-bottom: 24px; padding-bottom: 20px;
      border-bottom: 1px solid #f0f0f0; font-size: 13px; color: #999;
    }
    .blog_detail_meta span { display: flex; align-items: center; gap: 6px; }
    .blog_detail_meta a { color: #2f49d1; text-decoration: none; }
    .blog_detail_meta a:hover { text-decoration: underline; }

    /* Corps de l'article (HTML riche possible) */
    .blog_detail_body {
      font-size: 15px; line-height: 1.9; color: #444;
    }
    .blog_detail_body p   { margin-bottom: 18px; }
    .blog_detail_body h2  { font-size: 20px; font-weight: 700; margin: 32px 0 14px; color: #171717; }
    .blog_detail_body h3  { font-size: 17px; font-weight: 700; margin: 24px 0 10px; color: #171717; }
    .blog_detail_body img { max-width: 100%; border-radius: 6px; margin: 20px 0; }
    .blog_detail_body blockquote {
      border-left: 4px solid #2f49d1; padding: 12px 20px;
      margin: 24px 0; background: #f5f7ff; color: #555;
      font-style: italic; border-radius: 0 6px 6px 0;
    }
    .blog_detail_body a { color: #2f49d1; }

    /* ── Tags ── */
    .blog_tags { margin-top: 28px; padding-top: 20px; border-top: 1px solid #f0f0f0; }
    .blog_tags span { font-size: 13px; font-weight: 700; color: #555; margin-right: 8px; }
    .tag_chip {
      display: inline-block; font-size: 11px; font-weight: 600;
      padding: 4px 12px; border-radius: 20px; margin: 4px 4px 4px 0;
      border: 1.5px solid #e0e0e0; color: #777; text-decoration: none;
      transition: all .2s;
    }
    .tag_chip:hover { border-color: #2f49d1; color: #2f49d1; }

    /* ── Bouton retour ── */
    .btn_back {
      display: inline-flex; align-items: center; gap: 7px;
      font-size: 13px; font-weight: 600; color: #2f49d1 !important;
      text-decoration: none; margin-bottom: 28px; transition: gap .2s;
    }
    .btn_back:hover { gap: 10px; }

    /* ── Sidebar identique à blog.php ── */
    .post_item {
      display: flex; gap: 14px; align-items: flex-start;
      margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #f0f0f0;
    }
    .post_item:last-child { border-bottom: none; margin-bottom: 0; }
    .post_item img { width: 68px; height: 68px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
    .post_item .post_thumb_ph { width: 68px; height: 68px; border-radius: 6px; flex-shrink: 0; background: linear-gradient(135deg,#eff0f6,#dde2f7); }
    .post_item .media-body h3 { font-size: 13px; font-weight: 600; line-height: 1.4; margin-bottom: 4px; color: #171717; }
    .post_item .media-body h3 a { color: inherit; text-decoration: none; }
    .post_item .media-body h3 a:hover { color: #2f49d1; }
    .post_item .media-body p { font-size: 11px; color: #b9b9b9; margin: 0; }
  </style>
</head>

<body>
  <?php require_once '../includes/header.php'; ?>

  <!-- Breadcrumb -->
  <section class="breadcrumb breadcrumb_bg">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="breadcrumb_iner">
            <div class="breadcrumb_iner_item">
              <h2><?= htmlspecialchars($blog['titre']) ?></h2>
             
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!--================ Article =================-->
  <section class="blog_area section_padding">
    <div class="container">
      <div class="row">

        <!-- ══ CONTENU PRINCIPAL ══ -->
        <div class="col-lg-8 mb-5 mb-lg-0">
          <div class="blog_detail_content">

            <!-- Image couverture -->
            <?php if (!empty($blog['photo_couverture'])): ?>
              <img class="blog_detail_cover"
                   src="../<?= htmlspecialchars(ltrim($blog['photo_couverture'], '/')) ?>"
                   alt="<?= htmlspecialchars($blog['titre']) ?>">
            <?php endif; ?>

            <!-- Titre -->
            <h1><?= htmlspecialchars($blog['titre']) ?></h1>

            <!-- Meta -->
            <div class="blog_detail_meta">
              <span>
                <i class="far fa-user"></i>
                <?= htmlspecialchars($blog['auteur'] ?? 'Juste-Cœur Beaubrun') ?>
              </span>
              <span>
                <i class="far fa-calendar-alt"></i>
                <?= $dateObj->format('d M Y') ?>
              </span>
              <?php if (!empty($blog['categorie'])): ?>
                <span>
                  <i class="far fa-folder"></i>
                  <a href="/blog?cat=<?= urlencode($blog['categorie']) ?>">
                    <?= htmlspecialchars($blog['categorie']) ?>
                  </a>
                </span>
              <?php endif; ?>
            </div>

            <!-- Intro (si présente) -->
            <?php if (!empty($blog['intro'])): ?>
              <p style="font-size:16px;font-weight:600;color:#555;line-height:1.7;margin-bottom:24px;">
                <?= nl2br(htmlspecialchars($blog['intro'])) ?>
              </p>
            <?php endif; ?>

            <!-- Corps principal (HTML autorisé si vous faites confiance à l'éditeur) -->
            <div class="blog_detail_body">
              <?= $blog['contenu'] /* Le contenu vient d'un éditeur admin — si vous n'avez pas d'éditeur riche, remplacez par nl2br(htmlspecialchars(...)) */ ?>
            </div>

            <!-- Tags -->
            <?php if (!empty($tags)): ?>
              <div class="blog_tags">
                <span>Tags :</span>
                <?php foreach ($tags as $tag): ?>
                  <a href="/blog?q=<?= urlencode($tag) ?>" class="tag_chip">
                    <?= htmlspecialchars($tag) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

          </div><!-- /blog_detail_content -->
        </div><!-- /col-lg-8 -->

        <!-- ══ SIDEBAR ══ -->
        <div class="col-lg-4">
          <div class="blog_right_sidebar">

            <!-- Recherche -->
            <aside class="single_sidebar_widget search_widget">
              <form action="/blog" method="GET">
                <div class="form-group">
                  <div class="input-group mb-3">
                    <input type="text" name="q" class="form-control"
                           placeholder="Rechercher…"
                           onfocus="this.placeholder=''"
                           onblur="this.placeholder='Rechercher…'">
                    <div class="input-group-append">
                      <button class="btn_1" type="submit"><i class="ti-search"></i></button>
                    </div>
                  </div>
                </div>
                <button class="button rounded-0 primary-bg text-white w-100 btn_1" type="submit">
                  Rechercher
                </button>
              </form>
            </aside>

            <!-- Catégories -->
            <?php if (!empty($categories)): ?>
              <aside class="single_sidebar_widget post_category_widget">
                <h4 class="widget_title">Catégories</h4>
                <ul class="list cat-list">
                  <?php foreach ($categories as $cat): ?>
                    <li>
                      <a href="/blog?cat=<?= urlencode($cat) ?>" class="d-flex">
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

          </div><!-- /blog_right_sidebar -->
        </div><!-- /col-lg-4 -->

      </div><!-- /row -->
    </div><!-- /container -->
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
         

                <a  href="<?php echo base_url('projet'); ?>" class="btn btn-white mt-4 px-4 py-2">Découvrir ses engagements</a>
                
                <a  href="<?php echo base_url('contact'); ?>" class="mt-3 btn btn-primary mt-4 px-4 py-2">Proposer une collaboration</a>
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
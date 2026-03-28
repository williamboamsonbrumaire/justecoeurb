<?php
require_once __DIR__ . '/model/reviews-crud.php';

// Filtre statut
$statutFilter = trim($_GET['statut'] ?? '');

// Données
$reviews = getAllReviews($conDB, $statutFilter);

// Stats
try {
    $stmtStats = $conDB->query("SELECT statut, COUNT(*) as nb FROM reviews GROUP BY statut");
    $statsRaw  = $stmtStats->fetchAll(PDO::FETCH_ASSOC);
    $stats     = ['actif' => 0, 'inactif' => 0];
    foreach ($statsRaw as $s) $stats[$s['statut']] = (int)$s['nb'];
    $totalAll  = array_sum($stats);
} catch (Exception $e) {
    $stats    = ['actif' => 0, 'inactif' => 0];
    $totalAll = 0;
}

// Messages flash
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
  <title>Témoignages | Juste Cœur BeauBrun</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>

    /* ═══════════════════════════════════════════
       ALERTS
    ═══════════════════════════════════════════ */
    .alert {
      padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 500;
      margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .alert-success { background: rgba(75,222,151,.12); color: #27a96c; border: 1px solid rgba(75,222,151,.3); }
    .alert-danger  { background: rgba(242,100,100,.10); color: #e04b4b; border: 1px solid rgba(242,100,100,.25); }

    /* ═══════════════════════════════════════════
       HERO
    ═══════════════════════════════════════════ */
    .rv-hero {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      border-radius: 16px;
      padding: 36px 36px;
      margin-bottom: 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      overflow: hidden;
      position: relative;
    }
    .rv-hero::before {
      content: '';
      position: absolute;
      width: 340px; height: 340px; border-radius: 50%;
      background: rgba(99,179,237,.07);
      right: -80px; top: -100px; pointer-events: none;
    }
    .rv-hero::after {
      content: '';
      position: absolute;
      width: 180px; height: 180px; border-radius: 50%;
      background: rgba(99,179,237,.04);
      right: 160px; bottom: -60px; pointer-events: none;
    }
    /* Guillemet décoratif */
    .rv-hero__deco {
      position: absolute;
      font-size: 220px;
      font-family: Georgia, serif;
      color: rgba(255,255,255,.03);
      line-height: 1;
      top: -20px; left: 24px;
      pointer-events: none;
      user-select: none;
    }
    .rv-hero__text { position: relative; z-index: 1; }
    .rv-hero__text h1 {
      font-size: 25px; font-weight: 800; color: #fff;
      letter-spacing: .3px; margin-bottom: 8px; line-height: 1.3;
    }
    .rv-hero__text p {
      font-size: 13px; color: rgba(255,255,255,.6); line-height: 1.65; max-width: 380px;
    }
    .rv-hero__right {
      display: flex; flex-direction: column; align-items: flex-end;
      gap: 14px; flex-shrink: 0; position: relative; z-index: 1;
    }
    .rv-hero__stats { display: flex; gap: 12px; }
    .rv-hero__stat {
      text-align: center;
      background: rgba(255,255,255,.1);
      border: 1px solid rgba(255,255,255,.15);
      border-radius: 14px;
      padding: 13px 20px;
      backdrop-filter: blur(8px);
    }
    .rv-hero__stat strong { display: block; font-size: 26px; font-weight: 800; color: #fff; line-height: 1; }
    .rv-hero__stat span   { font-size: 10px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; display: block; }
    .btn-new-review {
      display: inline-flex; align-items: center; gap: 7px;
      background: #fff; color: #1a1a2e;
      font-size: 13px; font-weight: 700;
      padding: 10px 20px; border-radius: 10px;
      text-decoration: none; transition: all .2s; white-space: nowrap;
      box-shadow: 0 4px 16px rgba(0,0,0,.2);
    }
    .btn-new-review:hover { background: #e8f0ff; transform: translateY(-1px); }
    @media (max-width: 768px) {
      .rv-hero { flex-direction: column; align-items: flex-start; }
      .rv-hero__right { align-items: flex-start; width: 100%; }
      .rv-hero__stats { width: 100%; }
      .rv-hero__stat  { flex: 1; }
    }

    /* ═══════════════════════════════════════════
       TOOLBAR
    ═══════════════════════════════════════════ */
    .rv-toolbar {
      display: flex; align-items: center; gap: 10px;
      flex-wrap: wrap; margin-bottom: 22px;
    }
    .statut-tabs {
      display: flex; align-items: center; gap: 4px;
      background: #f4f5fb; border-radius: 10px; padding: 4px;
    }
    .statut-tab {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 6px 14px; border-radius: 7px; font-size: 12px; font-weight: 600;
      color: #767676; text-decoration: none; transition: all .2s; white-space: nowrap;
    }
    .statut-tab:hover { color: #1a1a2e; }
    .statut-tab.active { background: #fff; color: #171717; box-shadow: 0 1px 6px rgba(0,0,0,.08); }
    .statut-tab .tab-count {
      font-size: 10px; font-weight: 700;
      background: #e8eaf8; color: #2f49d1;
      padding: 1px 6px; border-radius: 10px;
    }
    .statut-tab.active .tab-count { background: #1a1a2e; color: #fff; }

    /* ═══════════════════════════════════════════
       GRILLE TESTIMONIALS
    ═══════════════════════════════════════════ */
    .rv-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
    }
    @media (max-width: 1100px) { .rv-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 640px)  { .rv-grid { grid-template-columns: 1fr; } }

    /* ═══════════════════════════════════════════
       REVIEW CARD
    ═══════════════════════════════════════════ */
    .rv-card {
      background: #fff;
      border-radius: 16px;
      border: 1.5px solid #f0f1f8;
      box-shadow: 0 2px 16px rgba(26,26,46,.06);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: transform .3s, box-shadow .3s, border-color .3s;
      position: relative;
    }
    .rv-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 16px 40px rgba(26,26,46,.12);
      border-color: #c5cdef;
    }
    .rv-card--inactive {
      opacity: .65;
      border-style: dashed;
      border-color: #e0e0e0;
    }

    /* Guillemet décoratif sur la carte */
    .rv-card__quote-deco {
      position: absolute;
      top: 14px; right: 18px;
      font-size: 64px;
      font-family: Georgia, serif;
      color: rgba(26,26,46,.06);
      line-height: 1;
      pointer-events: none;
      user-select: none;
    }

    /* En-tête carte : photo + nom + rôle */
    .rv-card__header {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 20px 20px 0;
    }
    .rv-card__avatar {
      width: 54px; height: 54px;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
      border: 2px solid #e8eaf8;
      background: linear-gradient(135deg, #1a1a2e, #0f3460);
    }
    .rv-card__avatar-placeholder {
      width: 54px; height: 54px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1a1a2e, #0f3460);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 20px; font-weight: 700;
      flex-shrink: 0;
    }
    .rv-card__info { min-width: 0; }
    .rv-card__name {
      font-size: 14px; font-weight: 700; color: #171717;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .rv-card__role {
      font-size: 11.5px; color: #767676; line-height: 1.4;
      margin-top: 2px;
    }
    .rv-card__org {
      font-size: 10.5px; color: #2f49d1; font-weight: 600;
      margin-top: 2px;
    }

    /* Badge statut */
    .rv-card__status {
      position: absolute;
      top: 14px; left: 14px;
      font-size: 10px; font-weight: 700; letter-spacing: .6px;
      text-transform: uppercase; padding: 3px 10px; border-radius: 20px;
      display: inline-flex; align-items: center; gap: 5px;
    }
    .rv-card__status--active {
      background: rgba(75,222,151,.15); color: #27a96c;
      border: 1px solid rgba(75,222,151,.35);
    }
    .rv-card__status--inactive {
      background: rgba(160,160,160,.12); color: #999;
      border: 1px solid rgba(160,160,160,.25);
    }
    .rv-card__status-dot {
      width: 6px; height: 6px; border-radius: 50%; display: inline-block;
    }
    .rv-card__status--active   .rv-card__status-dot { background: #4bde97; }
    .rv-card__status--inactive .rv-card__status-dot { background: #ccc; }

    /* Badge ordre */
    .rv-card__order {
      position: absolute;
      top: 42px; left: 14px;
      font-size: 10px; font-weight: 700;
      background: #1a1a2e; color: #fff;
      padding: 2px 8px; border-radius: 10px;
    }

    /* Corps : citation */
    .rv-card__body {
      padding: 16px 20px 20px;
      flex: 1;
      display: flex; flex-direction: column;
    }
    .rv-card__quote {
      font-size: 12.5px;
      line-height: 1.72;
      color: #5a5a72;
      font-style: italic;
      display: -webkit-box;
      -webkit-line-clamp: 5;
      -webkit-box-orient: vertical;
      overflow: hidden;
      flex: 1;
      margin-top: 14px;
    }

    /* Pied de carte */
    .rv-card__footer {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 7px;
      padding: 12px 16px;
      border-top: 1px solid #f0f1f8;
    }
    .btn-action {
      display: inline-flex; align-items: center; justify-content: center;
      width: 32px; height: 32px; border-radius: 8px;
      border: none; cursor: pointer; transition: all .2s;
      text-decoration: none; flex-shrink: 0;
    }
    .btn-action svg { width: 14px; height: 14px; }
    .btn-action--edit   { background: rgba(26,26,46,.07); color: #1a1a2e; }
    .btn-action--edit:hover {
      background: #1a1a2e; color: #fff;
      transform: translateY(-1px); box-shadow: 0 4px 12px rgba(26,26,46,.3);
    }
    .btn-action--toggle { background: rgba(255,182,72,.1); color: #c97b00; }
    .btn-action--toggle:hover { background: #ffb648; color: #fff; transform: translateY(-1px); }
    .btn-action--toggle.is-active { background: rgba(75,222,151,.1); color: #27a96c; }
    .btn-action--toggle.is-active:hover { background: #4bde97; color: #fff; }
    .btn-action--delete { background: rgba(242,100,100,.08); color: #e04b4b; }
    .btn-action--delete:hover {
      background: #e04b4b; color: #fff;
      transform: translateY(-1px); box-shadow: 0 4px 12px rgba(242,100,100,.3);
    }

    /* ═══════════════════════════════════════════
       EMPTY STATE
    ═══════════════════════════════════════════ */
    .rv-empty {
      grid-column: 1 / -1; text-align: center; padding: 70px 20px;
    }
    .rv-empty__icon {
      width: 80px; height: 80px; background: rgba(26,26,46,.07);
      border-radius: 50%; display: flex; align-items: center;
      justify-content: center; margin: 0 auto 16px;
    }
    .rv-empty__icon svg { width: 36px; height: 36px; color: #1a1a2e; opacity: .3; }
    .rv-empty h3 { font-size: 16px; color: #171717; margin-bottom: 6px; font-weight: 600; }
    .rv-empty p  { font-size: 13px; color: #b9b9b9; line-height: 1.6; }

    /* ═══════════════════════════════════════════
       MODALE SUPPRESSION
    ═══════════════════════════════════════════ */
    .modal-overlay {
      position: fixed; inset: 0;
      background: rgba(0,0,0,.5);
      backdrop-filter: blur(5px);
      z-index: 9999;
      display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
      background: #fff; border-radius: 18px; padding: 32px 28px;
      max-width: 420px; width: 100%; text-align: center;
      box-shadow: 0 24px 64px rgba(0,0,0,.18);
      animation: modalIn .25s ease;
    }
    @keyframes modalIn { from { transform: scale(.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .modal-icon {
      width: 60px; height: 60px; border-radius: 50%;
      background: rgba(242,100,100,.1);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px;
    }
    .modal-icon svg { width: 28px; height: 28px; color: #e04b4b; }
    .modal-box h3  { font-size: 18px; font-weight: 700; color: #171717; margin-bottom: 8px; }
    .modal-box p   { font-size: 13.5px; color: #767676; line-height: 1.6; margin-bottom: 24px; }
    .modal-actions { display: flex; gap: 10px; }
    .modal-cancel  {
      flex: 1; padding: 11px; border-radius: 9px; border: 1.5px solid #EEEEEE;
      background: #fff; font-size: 14px; font-weight: 600; color: #767676; cursor: pointer; transition: all .2s;
    }
    .modal-cancel:hover { border-color: #b9b9b9; color: #171717; }
    .modal-confirm {
      flex: 1; padding: 11px; border-radius: 9px; border: none;
      background: #e04b4b; font-size: 14px; font-weight: 700; color: #fff; cursor: pointer; transition: all .2s;
    }
    .modal-confirm:hover { background: #c93c3c; }

    /* ═══════════════════════════════════════════
       DARK MODE
    ═══════════════════════════════════════════ */
    .darkmode .rv-card              { background: #222235; border-color: #2c2c42; }
    .darkmode .rv-card--inactive    { border-color: #37374f; }
    .darkmode .rv-card__name        { color: #EFF0F6; }
    .darkmode .rv-card__quote       { color: #a8a9c0; }
    .darkmode .rv-card__footer      { border-color: #2c2c42; }
    .darkmode .statut-tabs          { background: #1a1a2e; }
    .darkmode .statut-tab.active    { background: #222235; color: #EFF0F6; }
    .darkmode .modal-box            { background: #222235; }
    .darkmode .modal-box h3         { color: #EFF0F6; }
    .darkmode .modal-cancel         { background: #2c2c42; border-color: #37374F; color: #D6D7E3; }
    .darkmode .btn-action--edit     { background: rgba(255,255,255,.07); color: #D6D7E3; }
    .darkmode .rv-card__quote-deco  { color: rgba(255,255,255,.04); }
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
          <div class="rv-hero">
            <div class="rv-hero__deco">"</div>
            <div class="rv-hero__text">
              <h1>Références & Témoignages</h1>
              <p>Gérez les témoignages affichés sur le site. Les inactifs sont masqués du public.</p>
            </div>
            <div class="rv-hero__right">
              <a href="add-review.php" class="btn-new-review">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouveau témoignage
              </a>
              <div class="rv-hero__stats">
                <div class="rv-hero__stat">
                  <strong><?= $totalAll ?></strong>
                  <span>Total</span>
                </div>
                <div class="rv-hero__stat">
                  <strong><?= $stats['actif'] ?></strong>
                  <span>Actifs</span>
                </div>
                <div class="rv-hero__stat">
                  <strong><?= $stats['inactif'] ?></strong>
                  <span>Inactifs</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ══════ FLASH ══════ -->
          <?php if ($flashSuccess): ?>
            <div class="alert alert-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              <?= htmlspecialchars($flashSuccess) ?>
            </div>
          <?php endif; ?>
          <?php if ($flashError): ?>
            <div class="alert alert-danger">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?= htmlspecialchars($flashError) ?>
            </div>
          <?php endif; ?>
          <?php if ($msg_success): ?>
            <div class="alert alert-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              <?= htmlspecialchars($msg_success) ?>
            </div>
          <?php endif; ?>
          <?php if ($msg_error): ?>
            <div class="alert alert-danger">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?= htmlspecialchars($msg_error) ?>
            </div>
          <?php endif; ?>

          <!-- ══════ TOOLBAR ══════ -->
          <div class="rv-toolbar">
            <div class="statut-tabs">
              <a href="?" class="statut-tab <?= $statutFilter === '' ? 'active' : '' ?>">
                Tous <span class="tab-count"><?= $totalAll ?></span>
              </a>
              <a href="?statut=actif" class="statut-tab <?= $statutFilter === 'actif' ? 'active' : '' ?>">
                Actifs <span class="tab-count"><?= $stats['actif'] ?></span>
              </a>
              <a href="?statut=inactif" class="statut-tab <?= $statutFilter === 'inactif' ? 'active' : '' ?>">
                 Inactifs <span class="tab-count"><?= $stats['inactif'] ?></span>
              </a>
            </div>
          </div>

          <!-- ══════ GRILLE ══════ -->
          <div class="rv-grid">

            <?php if (empty($reviews)): ?>
              <div class="rv-empty">
                <div class="rv-empty__icon">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                  </svg>
                </div>
                <h3>Aucun témoignage trouvé</h3>
                <p>Ajoutez votre premier témoignage avec le bouton ci-dessus.</p>
              </div>

            <?php else: ?>
              <?php foreach ($reviews as $rv):
                $initiale  = strtoupper(mb_substr(trim($rv['nom']), 0, 1));
                $isActive  = $rv['statut'] === 'actif';
                $hasPhoto  = !empty($rv['photo']);
                $imgSrc    = $hasPhoto ? '../../' . $rv['photo'] : null;
              ?>

              <article class="rv-card <?= !$isActive ? 'rv-card--inactive' : '' ?>">

                <!-- Badge statut -->
                <span class="rv-card__status <?= $isActive ? 'rv-card__status--active' : 'rv-card__status--inactive' ?>">
                  <span class="rv-card__status-dot"></span>
                  <?= $isActive ? 'Actif' : 'Inactif' ?>
                </span>

                <!-- Badge ordre -->
                <span class="rv-card__order">#<?= (int)$rv['ordre'] ?></span>

                <!-- Guillemet déco -->
                <span class="rv-card__quote-deco">"</span>

                <!-- En-tête -->
                <div class="rv-card__header">
                  <?php if ($imgSrc): ?>
                    <img class="rv-card__avatar" src="<?= htmlspecialchars($imgSrc) ?>"
                         alt="<?= htmlspecialchars($rv['nom']) ?>" loading="lazy">
                  <?php else: ?>
                    <div class="rv-card__avatar-placeholder"><?= $initiale ?></div>
                  <?php endif; ?>
                  <div class="rv-card__info">
                    <div class="rv-card__name"><?= htmlspecialchars($rv['nom']) ?></div>
                    <div class="rv-card__role"><?= htmlspecialchars($rv['role']) ?></div>
                    <?php if (!empty($rv['organisation'])): ?>
                      <div class="rv-card__org"><?= htmlspecialchars($rv['organisation']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Citation -->
                <div class="rv-card__body">
                  <p class="rv-card__quote"><?= htmlspecialchars($rv['quote']) ?></p>
                </div>

                <!-- Actions -->
                <div class="rv-card__footer">

                  <!-- Toggle actif/inactif -->
                  <form method="POST" action="model/reviews-crud.php" style="display:contents;">
                    <input type="hidden" name="id_review" value="<?= (int)$rv['id_review'] ?>">
                    <button type="submit" name="toggle_status"
                            class="btn-action btn-action--toggle <?= $isActive ? 'is-active' : '' ?>"
                            title="<?= $isActive ? 'Désactiver' : 'Activer' ?>">
                      <?php if ($isActive): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                      <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                      <?php endif; ?>
                    </button>
                  </form>

                  <!-- Éditer -->
                  <a href="edit-review.php?id=<?= (int)$rv['id_review'] ?>"
                     class="btn-action btn-action--edit" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </a>

                  <!-- Supprimer -->
                  <button type="button" class="btn-action btn-action--delete"
                          title="Supprimer"
                          data-id="<?= (int)$rv['id_review'] ?>"
                          data-nom="<?= htmlspecialchars($rv['nom'], ENT_QUOTES) ?>"
                          onclick="openDeleteModal(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  </button>

                </div>
              </article>

              <?php endforeach; ?>
            <?php endif; ?>

          </div><!-- /rv-grid -->

        </div><!-- /container -->
      </main>

      <?php include "./inside/footer.php"; ?>
    </div>
  </div>

  <!-- ══════ MODALE SUPPRESSION ══════ -->
  <div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
      <div class="modal-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
      </div>
      <h3>Supprimer le témoignage ?</h3>
      <p id="deleteModalText">Cette action est irréversible.</p>
      <div class="modal-actions">
        <button class="modal-cancel" onclick="closeDeleteModal()">Annuler</button>
        <form method="POST" action="model/reviews-crud.php" id="deleteForm" style="flex:1;">
          <input type="hidden" name="id_review" id="deleteIdInput">
          <button type="submit" name="delete_review" class="modal-confirm" style="width:100%;">Supprimer</button>
        </form>
      </div>
    </div>
  </div>

 <script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
  <script>
    function openDeleteModal(btn) {
      document.getElementById('deleteIdInput').value = btn.dataset.id;
      document.getElementById('deleteModalText').textContent =
        'Le témoignage de « ' + btn.dataset.nom + ' » sera définitivement supprimé.';
      document.getElementById('deleteModal').classList.add('open');
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('open');
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
      if (e.target === this) closeDeleteModal();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
  </script>
</body>
</html>
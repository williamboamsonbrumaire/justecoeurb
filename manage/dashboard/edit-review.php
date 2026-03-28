<?php
/**
 * edit-review.php — Modifier un témoignage
 * Design identique à add-review.php
 */
require_once __DIR__ . '/model/reviews-crud.php';

// Récupérer l'entrée
$id     = (int)($_GET['id'] ?? 0);
$stored = $id > 0 ? getReviewById($conDB, $id) : null;

if (!$stored) {
    $_SESSION['flash_error'] = "Témoignage introuvable.";
    header('Location: reviews.php');
    exit;
}

// Après soumission réussie → flash + redirect
if ($msg_success && empty($msg_error)) {
    $_SESSION['flash_success'] = $msg_success;
    header('Location: reviews.php');
    exit;
}

$isEdit    = true;
$pageTitle = "Modifier le témoignage";
// Fusionner POST (si erreur) avec les données DB
$review = !empty($_POST) ? array_merge($stored, $_POST, ['photo' => $stored['photo']]) : $stored;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?> | Juste Cœur BeauBrun</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
    /* ══════════════════════════════════════════
       REVIEW FORM — Styles partagés add & edit
    ══════════════════════════════════════════ */

    /* Layout */
    .rv-form-layout {
      display: grid;
      grid-template-columns: 1fr 290px;
      gap: 20px;
      align-items: start;
    }
    @media (max-width: 991px) { .rv-form-layout { grid-template-columns: 1fr; } }

    /* Alerts */
    .alert {
      padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 500;
      margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
    }
    .alert-success { background: rgba(75,222,151,.12); color: #27a96c; border: 1px solid rgba(75,222,151,.3); }
    .alert-danger  { background: rgba(242,100,100,.10); color: #e04b4b; border: 1px solid rgba(242,100,100,.25); }

    /* Breadcrumb */
    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #b9b9b9; margin-bottom: 20px; }
    .breadcrumb a   { color: #1a1a2e; text-decoration: none; }
    .breadcrumb a:hover { color: #2f49d1; }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* Section header */
    .section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .section-header__icon {
      width: 42px; height: 42px; border-radius: 10px;
      background: rgba(26,26,46,.08); color: #1a1a2e;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .section-header__title { font-weight: 700; font-size: 19px; color: #171717; }
    .section-header__sub   { font-size: 12px; color: #b9b9b9; margin-top: 2px; }

    /* Form elements */
    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-label {
      font-weight: 600; font-size: 13px; color: #171717;
      display: block; margin-bottom: 7px; letter-spacing: .2px;
    }
    .form-label .req { color: #f26464; margin-left: 2px; }
    .form-label .opt { color: #b9b9b9; font-weight: 400; font-size: 11px; margin-left: 4px; }
    .form-input {
      width: 100%; height: 44px; border-radius: 8px;
      background: #eff0f6; border: 2px solid transparent !important;
      padding: 0 14px; font-size: 14px; color: #171717; transition: .2s;
    }
    .form-input:focus {
      outline: none; border-color: rgba(134,182,254,.6) !important;
      box-shadow: 0 0 0 3px rgba(134,182,254,.2);
    }
    .form-textarea {
      width: 100%; min-height: 140px; border-radius: 8px;
      background: #eff0f6; border: 2px solid transparent !important;
      padding: 10px 14px; font-size: 14px; color: #171717;
      resize: vertical; font-family: inherit; transition: .2s;
      line-height: 1.7;
    }
    .form-textarea:focus {
      outline: none; border-color: rgba(134,182,254,.6) !important;
      box-shadow: 0 0 0 3px rgba(134,182,254,.2);
    }
    .form-select {
      width: 100%; height: 44px; border-radius: 8px;
      background: #eff0f6; border: 2px solid transparent !important;
      padding: 0 14px; font-size: 14px; color: #171717;
      cursor: pointer; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23b9b9b9' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 14px center;
    }
    .form-select:focus {
      outline: none; border-color: rgba(134,182,254,.6) !important;
      box-shadow: 0 0 0 3px rgba(134,182,254,.2);
    }
    .char-count { font-size: 11px; color: #b9b9b9; text-align: right; margin-top: 4px; }
    .char-count.warn { color: #ffb648; }
    .char-count.over { color: #f26464; }

    /* Preview citation */
    .quote-preview {
      background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
      border-radius: 12px;
      padding: 20px;
      margin-top: 14px;
      position: relative;
      overflow: hidden;
    }
    .quote-preview__deco {
      position: absolute; top: -10px; right: 12px;
      font-size: 80px; font-family: Georgia, serif;
      color: rgba(255,255,255,.07); line-height: 1;
      pointer-events: none;
    }
    .quote-preview__text {
      font-size: 12.5px; color: rgba(255,255,255,.75); font-style: italic;
      line-height: 1.7; position: relative; z-index: 1;
    }
    .quote-preview__author {
      margin-top: 12px; font-size: 12px; color: rgba(255,255,255,.5);
      position: relative; z-index: 1;
    }
    .quote-preview__author strong { color: #fff; }
    .quote-preview__label {
      font-size: 10px; color: rgba(255,255,255,.4);
      text-transform: uppercase; letter-spacing: 1px;
      margin-bottom: 10px; display: block;
    }

    /* Statut toggle */
    .statut-toggle { display: flex; gap: 8px; }
    .statut-option { flex: 1; }
    .statut-option input[type="radio"] { display: none; }
    .statut-option label {
      display: flex; align-items: center; justify-content: center; gap: 7px;
      padding: 10px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600;
      border: 2px solid #eeeeee; background: #fff; color: #b9b9b9; transition: .2s; width: 100%;
    }
    .statut-option input:checked + label.inactif { border-color: #d1d5db; background: rgba(156,163,175,.08); color: #888; }
    .statut-option input:checked + label.actif   { border-color: #4bde97; background: rgba(75,222,151,.08); color: #27a96c; }

    /* Upload zone */
    .upload-zone {
      border: 2px dashed #D6D7E3; border-radius: 10px; padding: 24px 14px;
      text-align: center; cursor: pointer; transition: .25s; background: #eff0f6; position: relative;
    }
    .upload-zone:hover, .upload-zone.drag-over { border-color: #1a1a2e; background: rgba(26,26,46,.04); }
    .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; min-height: unset; background: transparent; border: none !important; box-shadow: none !important; }
    .upload-zone__icon {
      width: 40px; height: 40px; border-radius: 50%;
      background: rgba(26,26,46,.1); color: #1a1a2e;
      display: flex; align-items: center; justify-content: center; margin: 0 auto 8px;
    }
    .upload-zone__title { font-weight: 600; font-size: 13px; color: #171717; margin-bottom: 2px; }
    .upload-zone__sub   { font-size: 11px; color: #b9b9b9; }
    .upload-preview     { display: none; position: relative; border-radius: 8px; overflow: hidden; }
    .upload-preview img { width: 100%; height: 160px; object-fit: cover; display: block; border-radius: 50%; max-width: 120px; margin: 0 auto; }
    .upload-preview-rm  {
      position: absolute; top: 6px; right: 6px; width: 26px; height: 26px;
      border-radius: 50%; background: rgba(242,100,100,.9); border: none;
      color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; padding: 0;
    }

    /* Photo actuelle */
    .current-photo-wrap { text-align: center; margin-bottom: 12px; }
    .current-photo-wrap img {
      width: 90px; height: 90px; border-radius: 50%; object-fit: cover;
      border: 3px solid #e8eaf8; display: block; margin: 0 auto 10px;
    }
    .remove-current-photo {
      display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600;
      color: #e04b4b; background: rgba(242,100,100,.08); border: 1.5px solid rgba(242,100,100,.2);
      border-radius: 6px; padding: 4px 10px; cursor: pointer; transition: all .2s;
    }
    .remove-current-photo:hover { background: rgba(242,100,100,.15); }
    .remove-current-photo input { display: none; }

    /* Meta info (edit only) */
    .article-meta-info {
      background: rgba(26,26,46,.05); border: 1.5px solid rgba(26,26,46,.1);
      border-radius: 10px; padding: 12px 14px; margin-bottom: 14px;
      font-size: 12px; color: #767676; line-height: 1.7;
    }
    .article-meta-info strong { color: #1a1a2e; }

    /* Sidebar card */
    .sidebar-card {
      background: #fff; border-radius: 12px; padding: 18px;
      box-shadow: 0 2px 12px rgba(160,163,189,.08); margin-bottom: 16px;
    }
    .sidebar-card:last-child { margin-bottom: 0; }
    .sidebar-card__title { font-weight: 700; font-size: 14px; color: #171717; margin-bottom: 14px; }

    /* Buttons */
    .btn-save {
      width: 100%; min-height: 46px; font-size: 14px; font-weight: 700;
      border-radius: 8px; letter-spacing: .3px;
      background: #1a1a2e; color: #fff; border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      transition: all .2s;
    }
    .btn-save:hover { background: #0f3460; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,26,46,.25); }
    .divider { border: none; border-top: 1px solid #eeeeee; margin: 14px 0; }

    /* Ordre input */
    .ordre-input-wrap {
      display: flex; align-items: center; gap: 10px;
    }
    .ordre-input-wrap .form-input { width: 80px; flex-shrink: 0; text-align: center; }
    .ordre-hint { font-size: 11px; color: #b9b9b9; line-height: 1.5; }

    /* Dark mode */
    .darkmode .form-label           { color: #D6D7E3; }
    .darkmode .form-input,
    .darkmode .form-textarea,
    .darkmode .form-select          { background: #222235; color: #D6D7E3; }
    .darkmode .section-header__title{ color: #EFF0F6; }
    .darkmode .sidebar-card         { background: #222235; box-shadow: none; }
    .darkmode .sidebar-card__title  { color: #EFF0F6; }
    .darkmode .upload-zone          { background: #222235; border-color: #37374B; }
    .darkmode .upload-zone__title   { color: #EFF0F6; }
    .darkmode .statut-option label  { background: #222235; border-color: #37374B; color: #767676; }
    .darkmode .form                 { background: #161624; }
    .darkmode .divider              { border-color: #37374B; }
    .darkmode .article-meta-info    { background: rgba(26,26,46,.3); border-color: rgba(255,255,255,.1); }
    .darkmode .breadcrumb a         { color: #a0b4ff; }
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
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="reviews.php">Témoignages</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span><?= $pageTitle ?></span>
          </nav>

          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <h2 class="main-title" style="margin-bottom:0;"><?= $pageTitle ?></h2>
            <a href="reviews.php" class="secondary-default-btn" style="font-size:13px; padding:8px 16px;">
              ← Retour aux témoignages
            </a>
          </div>

          <!-- Alertes -->
          <?php if ($msg_error): ?>
            <div class="alert alert-danger">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?= htmlspecialchars($msg_error) ?>
            </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" id="reviewForm">
            <?php if ($isEdit): ?>
              <input type="hidden" name="id_review" value="<?= (int)$review['id_review'] ?>">
            <?php endif; ?>

            <div class="rv-form-layout">

              <!-- ══ Colonne principale ══ -->
              <div>
                <div class="form">
                  <div class="section-header">
                    <div class="section-header__icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </div>
                    <div>
                      <div class="section-header__title">Informations du témoin</div>
                      <div class="section-header__sub">Identité et citation de la personne</div>
                    </div>
                  </div>

                  <!-- Nom -->
                  <div class="form-group">
                    <label class="form-label" for="nom">Nom complet <span class="req">*</span></label>
                    <input type="text" id="nom" name="nom" class="form-input"
                           placeholder="Ex : Mireille Dextra"
                           maxlength="150"
                           value="<?= htmlspecialchars($review['nom'] ?? '') ?>" required>
                    <div class="char-count" id="nomCount">0 / 150</div>
                  </div>

                  <!-- Rôle -->
                  <div class="form-group">
                    <label class="form-label" for="role">
                      Titre / Poste <span class="req">*</span>
                    </label>
                    <input type="text" id="role" name="role" class="form-input"
                           placeholder="Ex : Enseignante - Auteure"
                           maxlength="255"
                           value="<?= htmlspecialchars($review['role'] ?? '') ?>" required>
                    <div class="char-count" id="roleCount">0 / 255</div>
                  </div>

                  <!-- Organisation -->
                  <div class="form-group">
                    <label class="form-label" for="organisation">
                      Organisation <span class="opt">— optionnel</span>
                    </label>
                    <input type="text" id="organisation" name="organisation" class="form-input"
                           placeholder="Ex : HELP, Banj, PLES…"
                           maxlength="200"
                           value="<?= htmlspecialchars($review['organisation'] ?? '') ?>">
                  </div>

                  <!-- Citation -->
                  <div class="form-group">
                    <label class="form-label" for="quote">
                      Témoignage <span class="req">*</span>
                      <span class="opt">— sans guillemets, ils sont ajoutés automatiquement</span>
                    </label>
                    <textarea id="quote" name="quote" class="form-textarea"
                              placeholder="Rédigez le témoignage ici…"
                              maxlength="2000" required><?= htmlspecialchars($review['quote'] ?? '') ?></textarea>
                    <div class="char-count" id="quoteCount">0 / 2000</div>

                    <!-- Aperçu live -->
                    <div class="quote-preview" id="quotePreview" style="<?= empty($review['quote']) ? 'display:none' : '' ?>">
                      <span class="quote-preview__deco">"</span>
                      <span class="quote-preview__label">Aperçu</span>
                      <p class="quote-preview__text" id="previewQuoteText">
                        <?= htmlspecialchars($review['quote'] ?? '') ?>
                      </p>
                      <div class="quote-preview__author">
                        — <strong id="previewName"><?= htmlspecialchars($review['nom'] ?? '') ?></strong>
                        <span id="previewRole"><?= !empty($review['role']) ? ', ' . htmlspecialchars($review['role']) : '' ?></span>
                      </div>
                    </div>
                  </div>

                </div><!-- .form -->
              </div>

              <!-- ══ Colonne latérale ══ -->
              <div>

                <!-- Publication -->
                <div class="sidebar-card">
                  <div class="sidebar-card__title">Publication</div>

                  <?php if ($isEdit): ?>
                  <div class="article-meta-info">
                    <strong>#<?= (int)$review['id_review'] ?></strong>
                    · Créé le <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                    <?php if (!empty($review['updated_at'])): ?>
                      <br>Modifié le <?= date('d/m/Y à H:i', strtotime($review['updated_at'])) ?>
                    <?php endif; ?>
                  </div>
                  <?php endif; ?>

                  <!-- Statut -->
                  <div class="form-group">
                    <label class="form-label">Statut <span class="req">*</span></label>
                    <div class="statut-toggle">
                      <div class="statut-option">
                        <input type="radio" id="statut_inactif" name="statut" value="inactif"
                               <?= ($review['statut'] ?? 'actif') === 'inactif' ? 'checked' : '' ?>>
                        <label for="statut_inactif" class="inactif">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                          Inactif
                        </label>
                      </div>
                      <div class="statut-option">
                        <input type="radio" id="statut_actif" name="statut" value="actif"
                               <?= ($review['statut'] ?? 'actif') === 'actif' ? 'checked' : '' ?>>
                        <label for="statut_actif" class="actif">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                          Actif
                        </label>
                      </div>
                    </div>
                  </div>

                  <!-- Ordre d'affichage -->
                  <div class="form-group">
                    <label class="form-label" for="ordre">Ordre d'affichage</label>
                    <div class="ordre-input-wrap">
                      <input type="number" id="ordre" name="ordre" class="form-input"
                             min="0" max="99"
                             value="<?= (int)($review['ordre'] ?? 0) ?>">
                      <span class="ordre-hint">0 = premier affiché. Les témoignages sont triés par ordre croissant.</span>
                    </div>
                  </div>

                  <hr class="divider">

                  <button type="submit"
                          name="<?= $isEdit ? 'update_review' : 'add_review' ?>"
                          class="btn-save">
                    <?php if ($isEdit): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                      Enregistrer les modifications
                    <?php else: ?>
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                      Ajouter le témoignage
                    <?php endif; ?>
                  </button>

                  <a href="reviews.php" class="secondary-default-btn" style="display:flex; align-items:center; justify-content:center; text-decoration:none; width:100%; min-height:40px; font-size:13px; font-weight:600; border-radius:8px; margin-top:8px;">
                    Annuler
                  </a>
                </div>

                <!-- Photo -->
                <div class="sidebar-card">
                  <div class="sidebar-card__title">
                    Photo
                    <span style="color:#b9b9b9; font-size:11px; font-weight:400;">(optionnel)</span>
                  </div>

                  <?php if ($isEdit && !empty($review['photo'])): ?>
                    <div class="current-photo-wrap" id="currentPhotoWrap">
                      <img src="../<?= htmlspecialchars($review['photo']) ?>"
                           alt="Photo actuelle" id="currentPhotoImg">
                      <label class="remove-current-photo" id="removeCurrentLabel">
                        <input type="checkbox" name="remove_photo" id="removePhotoCheck" value="1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Supprimer cette photo
                      </label>
                    </div>
                    <p style="font-size:11px; color:#b9b9b9; margin-bottom:10px; text-align:center;">Ou remplacez-la :</p>
                  <?php endif; ?>

                  <div class="upload-zone" id="uploadZone">
                    <input type="file" name="photo" id="photoInput" accept="image/*">
                    <div id="uploadPlaceholder">
                      <div class="upload-zone__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                      </div>
                      <p class="upload-zone__title">Photo du témoin</p>
                      <p class="upload-zone__sub">JPG, PNG, WEBP — 4 Mo max</p>
                    </div>
                    <div class="upload-preview" id="previewContainer">
                      <img src="" alt="Aperçu" id="previewImg">
                      <button type="button" class="upload-preview-rm" id="removePhoto">✕</button>
                    </div>
                  </div>
                </div>

              </div><!-- sidebar -->
            </div><!-- rv-form-layout -->
          </form>

        </div><!-- .container -->
      </main>

      <?php include "./inside/footer.php"; ?>
    </div>
  </div>

  <script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
  <script>
  // ══════════════════════════════════════════════
  // Compteurs de caractères
  // ══════════════════════════════════════════════
  function bindCounter(inputId, counterId, max) {
    const el = document.getElementById(inputId);
    const ct = document.getElementById(counterId);
    if (!el || !ct) return;
    function update() {
      const n = el.value.length;
      ct.textContent = n + ' / ' + max;
      ct.className   = 'char-count' + (n > max * .85 ? (n >= max ? ' over' : ' warn') : '');
    }
    el.addEventListener('input', update);
    update();
  }
  bindCounter('nom',   'nomCount',   150);
  bindCounter('role',  'roleCount',  255);
  bindCounter('quote', 'quoteCount', 2000);

  // ══════════════════════════════════════════════
  // Aperçu live de la citation
  // ══════════════════════════════════════════════
  const quoteTextarea  = document.getElementById('quote');
  const nomInput       = document.getElementById('nom');
  const roleInput      = document.getElementById('role');
  const previewSection = document.getElementById('quotePreview');
  const previewText    = document.getElementById('previewQuoteText');
  const previewName    = document.getElementById('previewName');
  const previewRole    = document.getElementById('previewRole');

  function updatePreview() {
    const q = quoteTextarea.value.trim();
    if (q) {
      previewSection.style.display = 'block';
      previewText.textContent      = q;
    } else {
      previewSection.style.display = 'none';
    }
    previewName.textContent = nomInput.value.trim() || 'Nom du témoin';
    const r = roleInput.value.trim();
    previewRole.textContent = r ? ', ' + r : '';
  }

  quoteTextarea.addEventListener('input', updatePreview);
  nomInput.addEventListener('input',      updatePreview);
  roleInput.addEventListener('input',     updatePreview);
  updatePreview();

  // ══════════════════════════════════════════════
  // Upload photo + prévisualisation (ronde)
  // ══════════════════════════════════════════════
  const photoInput        = document.getElementById('photoInput');
  const uploadZone        = document.getElementById('uploadZone');
  const uploadPlaceholder = document.getElementById('uploadPlaceholder');
  const previewContainer  = document.getElementById('previewContainer');
  const previewImg        = document.getElementById('previewImg');
  const removeBtn         = document.getElementById('removePhoto');

  photoInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      previewImg.src = e.target.result;
      uploadPlaceholder.style.display = 'none';
      previewContainer.style.display  = 'block';
      const wrap = document.getElementById('currentPhotoWrap');
      if (wrap) wrap.style.display = 'none';
    };
    reader.readAsDataURL(file);
  });

  if (removeBtn) {
    removeBtn.addEventListener('click', e => {
      e.stopPropagation();
      photoInput.value = '';
      previewImg.src   = '';
      previewContainer.style.display  = 'none';
      uploadPlaceholder.style.display = 'block';
      const wrap = document.getElementById('currentPhotoWrap');
      if (wrap) wrap.style.display = 'block';
    });
  }

  uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
  uploadZone.addEventListener('dragleave', ()  => uploadZone.classList.remove('drag-over'));
  uploadZone.addEventListener('drop', e => {
    e.preventDefault(); uploadZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
      const dt = new DataTransfer(); dt.items.add(file);
      photoInput.files = dt.files;
      photoInput.dispatchEvent(new Event('change'));
    }
  });

  // ══════════════════════════════════════════════
  // Supprimer photo actuelle (edit)
  // ══════════════════════════════════════════════
  const removePhotoCheck = document.getElementById('removePhotoCheck');
  const currentPhotoImg  = document.getElementById('currentPhotoImg');
  if (removePhotoCheck && currentPhotoImg) {
    removePhotoCheck.addEventListener('change', function () {
      currentPhotoImg.style.opacity = this.checked ? '.25' : '1';
      currentPhotoImg.style.filter  = this.checked ? 'grayscale(1)' : 'none';
    });
  }
  </script>
</body>
</html>
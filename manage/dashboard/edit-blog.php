<?php
require_once __DIR__ . '/model/blog-crud.php';

// Récupérer l'article à modifier
$id   = (int) ($_GET['id'] ?? 0);
$blog = $id > 0 ? getBlogById($conDB, $id) : null;

if (!$blog) {
    $_SESSION['flash_error'] = "Article introuvable.";
    header('Location: blogs.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier l'article | Juste Cœur BeauBrun</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <!-- Quill Editor -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">
  <style>
    /* ══ Layout two-column ══ */
    .blog-layout {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 20px;
      align-items: start;
    }
    @media (max-width: 991px) {
      .blog-layout { grid-template-columns: 1fr; }
    }

    /* ══ Alerts ══ */
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

    /* ══ Form elements ══ */
    .form-group      { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-label {
      font-weight: 600;
      font-size: 13px;
      color: #171717;
      display: block;
      margin-bottom: 7px;
      letter-spacing: 0.2px;
    }
    .form-label .req { color: #f26464; margin-left: 2px; }
    .form-label .opt { color: #b9b9b9; font-weight: 400; font-size: 11px; margin-left: 4px; }
    .form-input {
      width: 100%;
      height: 44px;
      border-radius: 8px;
      background: #eff0f6;
      border: 2px solid transparent !important;
      padding: 0 14px;
      font-size: 14px;
      color: #171717;
      transition: 0.2s;
    }
    .form-input:focus {
      outline: none;
      border-color: rgba(134,182,254,0.6) !important;
      box-shadow: 0 0 0 3px rgba(134,182,254,0.2);
    }
    .form-textarea {
      width: 100%;
      min-height: 90px;
      border-radius: 8px;
      background: #eff0f6;
      border: 2px solid transparent !important;
      padding: 10px 14px;
      font-size: 14px;
      color: #171717;
      resize: vertical;
      font-family: inherit;
      transition: 0.2s;
    }
    .form-textarea:focus {
      outline: none;
      border-color: rgba(134,182,254,0.6) !important;
      box-shadow: 0 0 0 3px rgba(134,182,254,0.2);
    }
    .form-select {
      width: 100%;
      height: 44px;
      border-radius: 8px;
      background: #eff0f6;
      border: 2px solid transparent !important;
      padding: 0 14px;
      font-size: 14px;
      color: #171717;
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23b9b9b9' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
    }
    .form-select:focus {
      outline: none;
      border-color: rgba(134,182,254,0.6) !important;
      box-shadow: 0 0 0 3px rgba(134,182,254,0.2);
    }
    .char-count { font-size: 11px; color: #b9b9b9; text-align: right; margin-top: 4px; }
    .char-count.warn { color: #ffb648; }
    .char-count.over { color: #f26464; }

    /* ══ Quill editor ══ */
    .quill-wrapper { border-radius: 8px; overflow: hidden; border: 2px solid transparent; transition: 0.2s; }
    .quill-wrapper:focus-within { border-color: rgba(134,182,254,0.6); box-shadow: 0 0 0 3px rgba(134,182,254,0.2); }
    .ql-toolbar.ql-snow {
      border: none !important;
      background: #e6e7f0;
      border-radius: 0 !important;
      padding: 8px 12px;
    }
    .ql-container.ql-snow {
      border: none !important;
      background: #eff0f6;
      font-family: inherit;
      font-size: 14px;
      min-height: 260px;
      border-radius: 0 !important;
    }
    .ql-editor { min-height: 260px; line-height: 1.7; color: #171717; }
    .ql-editor.ql-blank::before { color: #d6d7e3; font-style: normal; }

    /* ══ Upload zone ══ */
    .upload-zone {
      border: 2px dashed #D6D7E3;
      border-radius: 10px;
      padding: 28px 16px;
      text-align: center;
      cursor: pointer;
      transition: 0.25s;
      background: #eff0f6;
      position: relative;
    }
    .upload-zone:hover, .upload-zone.drag-over {
      border-color: #2f49d1;
      background: rgba(47,73,209,0.04);
    }
    .upload-zone input[type="file"] {
      position: absolute; inset: 0; opacity: 0; cursor: pointer;
      min-height: unset; background: transparent; border: none !important; box-shadow: none !important;
    }
    .upload-zone__icon {
      width: 44px; height: 44px; border-radius: 50%;
      background: rgba(47,73,209,0.1); color: #2f49d1;
      display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;
    }
    .upload-zone__title { font-weight: 600; font-size: 13px; color: #171717; margin-bottom: 3px; }
    .upload-zone__sub   { font-size: 11px; color: #b9b9b9; }
    .upload-preview     { display: none; position: relative; border-radius: 8px; overflow: hidden; }
    .upload-preview img { width: 100%; height: 180px; object-fit: cover; display: block; border-radius: 8px; }
    .upload-preview-rm  {
      position: absolute; top: 8px; right: 8px; width: 26px; height: 26px;
      border-radius: 50%; background: rgba(242,100,100,0.9); border: none;
      color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
      font-size: 14px; padding: 0;
    }

    /* ══ Photo actuelle ══ */
    .current-photo-wrap {
      margin-bottom: 12px;
    }
    .current-photo-wrap img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 8px;
      display: block;
      margin-bottom: 8px;
    }
    .remove-current-photo {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 600;
      color: #e04b4b;
      background: rgba(242,100,100,.08);
      border: 1.5px solid rgba(242,100,100,.2);
      border-radius: 6px;
      padding: 4px 10px;
      cursor: pointer;
      transition: all .2s;
    }
    .remove-current-photo:hover { background: rgba(242,100,100,.15); }
    .remove-current-photo input { display: none; }

    /* ══ Tags input ══ */
    .tags-input-wrapper {
      display: flex; flex-wrap: wrap; gap: 6px;
      background: #eff0f6; border-radius: 8px; padding: 8px 10px;
      border: 2px solid transparent; cursor: text; min-height: 44px; align-items: center;
      transition: 0.2s;
    }
    .tags-input-wrapper:focus-within { border-color: rgba(134,182,254,0.6) !important; box-shadow: 0 0 0 3px rgba(134,182,254,0.2); }
    .tag-pill {
      display: flex; align-items: center; gap: 4px;
      background: rgba(47,73,209,0.1); color: #2f49d1;
      padding: 3px 10px 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .tag-pill button {
      border: none; background: transparent; color: #2f49d1; cursor: pointer;
      font-size: 14px; line-height: 1; padding: 0; margin-left: 2px;
    }
    .tags-real-input { border: none !important; background: transparent; min-width: 80px; flex: 1; font-size: 13px; padding: 0 4px; box-shadow: none !important; min-height: 24px; height: 24px; }
    .tags-real-input:focus { box-shadow: none !important; border: none !important; }

    /* ══ Statut toggle ══ */
    .statut-toggle { display: flex; gap: 8px; }
    .statut-option { flex: 1; }
    .statut-option input[type="radio"] { display: none; }
    .statut-option label {
      display: flex; align-items: center; justify-content: center; gap: 7px;
      padding: 10px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600;
      border: 2px solid #eeeeee; background: #fff; color: #b9b9b9; transition: 0.2s;
      width: 100%;
    }
    .statut-option input:checked + label.brouillon { border-color: #ffb648; background: rgba(255,182,72,0.08); color: #ffb648; }
    .statut-option input:checked + label.publie    { border-color: #4bde97; background: rgba(75,222,151,0.08); color: #27a96c; }

    /* ══ Section header ══ */
    .section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .section-header__icon {
      width: 42px; height: 42px; border-radius: 10px;
      background: rgba(47,73,209,0.1); color: #2f49d1;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .section-header__title { font-weight: 700; font-size: 19px; color: #171717; }
    .section-header__sub   { font-size: 12px; color: #b9b9b9; margin-top: 2px; }

    /* ══ Breadcrumb ══ */
    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #b9b9b9; margin-bottom: 20px; }
    .breadcrumb a   { color: #2f49d1; }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* ══ Sidebar card ══ */
    .sidebar-card {
      background: #fff; border-radius: 12px; padding: 18px;
      box-shadow: 0 2px 12px rgba(160,163,189,0.08); margin-bottom: 16px;
    }
    .sidebar-card__title { font-weight: 700; font-size: 14px; color: #171717; margin-bottom: 14px; }
    .sidebar-card:last-child { margin-bottom: 0; }

    /* ══ Info article existant ══ */
    .article-meta-info {
      background: rgba(47,73,209,.05);
      border: 1.5px solid rgba(47,73,209,.12);
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 14px;
      font-size: 12px;
      color: #767676;
      line-height: 1.7;
    }
    .article-meta-info strong { color: #2f49d1; }

    /* ══ Buttons ══ */
    .btn-publish {
      width: 100%; min-height: 46px; font-size: 14px; font-weight: 700;
      border-radius: 8px; letter-spacing: 0.3px;
    }
    .btn-draft {
      width: 100%; min-height: 40px; font-size: 13px; font-weight: 600;
      border-radius: 8px; margin-top: 8px;
    }
    .divider { border: none; border-top: 1px solid #eeeeee; margin: 14px 0; }

    /* ══ Word count ══ */
    .word-count-bar {
      display: flex; align-items: center; justify-content: space-between;
      background: #eff0f6; border-radius: 0 0 8px 8px;
      padding: 5px 14px; font-size: 11px; color: #b9b9b9;
    }

    /* ══ Dark mode ══ */
    .darkmode .form-label          { color: #D6D7E3; }
    .darkmode .form-input,
    .darkmode .form-textarea,
    .darkmode .form-select         { background: #222235; color: #D6D7E3; }
    .darkmode .section-header__title { color: #EFF0F6; }
    .darkmode .sidebar-card        { background: #222235; box-shadow: none; }
    .darkmode .sidebar-card__title { color: #EFF0F6; }
    .darkmode .ql-toolbar.ql-snow  { background: #2a2a3e; }
    .darkmode .ql-container.ql-snow { background: #222235; }
    .darkmode .ql-editor           { color: #D6D7E3; }
    .darkmode .upload-zone         { background: #222235; border-color: #37374B; }
    .darkmode .upload-zone__title  { color: #EFF0F6; }
    .darkmode .upload-zone:hover   { border-color: #5887ff; }
    .darkmode .tags-input-wrapper  { background: #222235; }
    .darkmode .statut-option label { background: #222235; border-color: #37374B; color: #767676; }
    .darkmode .word-count-bar      { background: #2a2a3e; }
    .darkmode .form                { background: #161624; }
    .darkmode .divider             { border-color: #37374B; }
    .darkmode .article-meta-info   { background: rgba(47,73,209,.1); border-color: rgba(47,73,209,.2); }
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
            <a href="blog.php">Blog</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            <span>Modifier l'article</span>
          </nav>

          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
            <h2 class="main-title" style="margin-bottom:0;">Modifier l'article</h2>
            <a href="blog.php" class="secondary-default-btn" style="font-size:13px; padding:8px 16px;">
              ← Retour aux articles
            </a>
          </div>

          <!-- Alertes globales -->
          <?php if ($msg_success): ?>
            <div class="alert alert-success">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <?= htmlspecialchars($msg_success) ?>
            </div>
          <?php endif; ?>
          <?php if ($msg_error): ?>
            <div class="alert alert-danger">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?= htmlspecialchars($msg_error) ?>
            </div>
          <?php endif; ?>

          <?php
          // Si POST réussi, recharger les données fraîches depuis la DB
          $data = !empty($_POST) ? array_merge($blog, $_POST) : $blog;
          ?>

          <form method="POST" enctype="multipart/form-data" id="blogForm">
            <!-- ID de l'article -->
            <input type="hidden" name="id_blog" value="<?= (int)$blog['id_blog'] ?>">

            <div class="blog-layout">

              <!-- ══ Colonne principale ══ -->
              <div>
                <div class="form">
                  <div class="section-header">
                    <div class="section-header__icon">
                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </div>
                    <div>
                      <div class="section-header__title">Modifier le contenu</div>
                      <div class="section-header__sub">Modifiez votre article avec l'éditeur riche</div>
                    </div>
                  </div>

                  <!-- Titre -->
                  <div class="form-group">
                    <label class="form-label" for="titre">Titre <span class="req">*</span></label>
                    <input type="text" id="titre" name="titre" class="form-input"
                           placeholder="Titre accrocheur de votre article…"
                           maxlength="255"
                           value="<?= htmlspecialchars($data['titre'] ?? '') ?>" required>
                    <div class="char-count" id="titreCount">0 / 255</div>
                  </div>

                  <!-- Intro -->
                  <div class="form-group">
                    <label class="form-label" for="intro">
                      Introduction <span class="req">*</span>
                      <span class="opt">— Résumé affiché dans la liste des articles</span>
                    </label>
                    <textarea id="intro" name="intro" class="form-textarea"
                              placeholder="Une ou deux phrases qui donnent envie de lire la suite…"
                              maxlength="600" required><?= htmlspecialchars($data['intro'] ?? '') ?></textarea>
                    <div class="char-count" id="introCount">0 / 600</div>
                  </div>

                  <!-- Contenu (Quill) -->
                  <div class="form-group">
                    <label class="form-label">Contenu complet <span class="req">*</span></label>
                    <div class="quill-wrapper">
                      <div id="quillEditor"></div>
                    </div>
                    <div class="word-count-bar">
                      <span id="wordCount">0 mot</span>
                      <span id="readTime">Temps de lecture : 0 min</span>
                    </div>
                    <input type="hidden" name="contenu" id="contenuHidden">
                  </div>

                </div><!-- .form -->
              </div>

              <!-- ══ Colonne latérale ══ -->
              <div>

                <!-- Publication -->
                <div class="sidebar-card">
                  <div class="sidebar-card__title">Publication</div>

                  <!-- Infos article -->
                  <div class="article-meta-info">
                    <strong>#<?= (int)$blog['id_blog'] ?></strong> · Créé le <?= date('d/m/Y', strtotime($blog['created_at'])) ?>
                    <?php if (!empty($blog['updated_at'])): ?>
                      <br>Modifié le <?= date('d/m/Y à H:i', strtotime($blog['updated_at'])) ?>
                    <?php endif; ?>
                  </div>

                  <!-- Statut -->
                  <div class="form-group">
                    <label class="form-label">Statut <span class="req">*</span></label>
                    <div class="statut-toggle">
                      <div class="statut-option">
                        <input type="radio" id="statut_brouillon" name="statut" value="brouillon"
                               <?= ($data['statut'] ?? 'brouillon') === 'brouillon' ? 'checked' : '' ?>>
                        <label for="statut_brouillon" class="brouillon">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          Brouillon
                        </label>
                      </div>
                      <div class="statut-option">
                        <input type="radio" id="statut_publie" name="statut" value="publié"
                               <?= ($data['statut'] ?? '') === 'publié' ? 'checked' : '' ?>>
                        <label for="statut_publie" class="publie">
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                          Publié
                        </label>
                      </div>
                    </div>
                  </div>

                  <hr class="divider">

                  <button type="submit" name="update_blog" class="primary-default-btn btn-publish">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:7px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Enregistrer les modifications
                  </button>

                  <a href="blogs.php" class="secondary-default-btn btn-draft" style="display:flex; align-items:center; justify-content:center; text-decoration:none; margin-top:8px;">
                    Annuler
                  </a>
                </div>

                <!-- Catégorie & Auteur -->
                <div class="sidebar-card">
                  <div class="sidebar-card__title">Informations</div>

                  <div class="form-group">
                    <label class="form-label" for="categorie">Catégorie <span class="req">*</span></label>
                    <select id="categorie" name="categorie" class="form-select" required>
                      <option value="" disabled>Choisir une catégorie</option>
                      <?php
                      $categoriesList = ['Actualité', 'Témoignage', 'Santé', 'Éducation', 'Communauté', 'Événement', 'Autre'];
                      foreach ($categoriesList as $cat):
                        $sel = ($data['categorie'] ?? '') === $cat ? 'selected' : '';
                      ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $sel ?>><?= htmlspecialchars($cat) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="auteur">Auteur <span class="req">*</span></label>
                    <input type="text" id="auteur" name="auteur" class="form-input"
                           placeholder="Nom de l'auteur"
                           maxlength="156"
                           value="<?= htmlspecialchars($data['auteur'] ?? '') ?>" required>
                  </div>
                </div>

                <!-- Photo de couverture -->
                <div class="sidebar-card">
                  <div class="sidebar-card__title">Photo de couverture <span style="color:#b9b9b9; font-size:11px; font-weight:400;">(optionnel)</span></div>

                  <?php if (!empty($blog['photo_couverture'])): ?>
                    <div class="current-photo-wrap" id="currentPhotoWrap">
                      <img src="../<?= htmlspecialchars($blog['photo_couverture']) ?>"
                           alt="Photo actuelle" id="currentPhotoImg">
                      <label class="remove-current-photo" id="removeCurrentLabel">
                        <input type="checkbox" name="remove_photo" id="removePhotoCheck" value="1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                        Supprimer cette photo
                      </label>
                    </div>
                    <p style="font-size:11px; color:#b9b9b9; margin-bottom:10px;">Ou remplacez-la ci-dessous :</p>
                  <?php endif; ?>

                  <div class="upload-zone" id="uploadZone">
                    <input type="file" name="photo_couverture" id="photoInput" accept="image/*">
                    <div id="uploadPlaceholder">
                      <div class="upload-zone__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                      </div>
                      <p class="upload-zone__title">Cliquez ou déposez</p>
                      <p class="upload-zone__sub">JPG, PNG, WEBP — 5 Mo max</p>
                    </div>
                    <div class="upload-preview" id="previewContainer">
                      <img src="" alt="Aperçu" id="previewImg">
                      <button type="button" class="upload-preview-rm" id="removePhoto">✕</button>
                    </div>
                  </div>
                </div>

                <!-- Tags -->
                <div class="sidebar-card">
                  <div class="sidebar-card__title">Tags <span style="color:#b9b9b9; font-size:11px; font-weight:400;">(optionnel)</span></div>
                  <div class="tags-input-wrapper" id="tagsWrapper">
                    <input type="text" id="tagsInput" class="tags-real-input" placeholder="Ajouter un tag, Entrée…">
                  </div>
                  <input type="hidden" name="tags" id="tagsHidden" value="<?= htmlspecialchars($data['tags'] ?? '') ?>">
                  <div style="font-size:11px; color:#b9b9b9; margin-top:6px;">Appuyez sur <kbd style="background:#eff0f6; padding:1px 5px; border-radius:3px; font-size:10px;">Entrée</kbd> ou <kbd style="background:#eff0f6; padding:1px 5px; border-radius:3px; font-size:10px;">,</kbd> pour ajouter</div>
                </div>

              </div>
            </div><!-- .blog-layout -->
          </form>

        </div><!-- .container -->
      </main>

      <?php include "./inside/footer.php"; ?>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
  <script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
  <script>
  // ══════════════════════════════════════════════
  // Quill Editor — chargé avec le contenu existant
  // ══════════════════════════════════════════════
  const quill = new Quill('#quillEditor', {
    theme: 'snow',
    placeholder: 'Rédigez votre article ici…',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ color: [] }, { background: [] }],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }],
        ['blockquote', 'code-block'],
        ['link', 'image'],
        ['clean']
      ]
    }
  });

  // Charger le contenu existant de la DB
  const existingContent = <?= json_encode(
    !empty($_POST['contenu']) ? $_POST['contenu'] : ($blog['contenu'] ?? '')
  ) ?>;
  if (existingContent) quill.root.innerHTML = existingContent;

  // Compteur de mots + temps de lecture
  function updateWordCount() {
    const text  = quill.getText().trim();
    const words = text ? text.split(/\s+/).length : 0;
    const mins  = Math.max(1, Math.round(words / 200));
    document.getElementById('wordCount').textContent = words + (words > 1 ? ' mots' : ' mot');
    document.getElementById('readTime').textContent  = 'Temps de lecture : ~' + mins + ' min';
  }
  quill.on('text-change', updateWordCount);
  updateWordCount();

  // Injecter le HTML dans le champ caché avant soumission
  document.getElementById('blogForm').addEventListener('submit', () => {
    document.getElementById('contenuHidden').value = quill.root.innerHTML;
  });

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
      ct.className = 'char-count' + (n > max * 0.85 ? (n >= max ? ' over' : ' warn') : '');
    }
    el.addEventListener('input', update);
    update();
  }
  bindCounter('titre', 'titreCount', 255);
  bindCounter('intro', 'introCount', 600);

  // ══════════════════════════════════════════════
  // Suppression photo actuelle
  // ══════════════════════════════════════════════
  const removePhotoCheck = document.getElementById('removePhotoCheck');
  const currentPhotoImg  = document.getElementById('currentPhotoImg');
  if (removePhotoCheck && currentPhotoImg) {
    removePhotoCheck.addEventListener('change', function () {
      if (this.checked) {
        currentPhotoImg.style.opacity = '.3';
        currentPhotoImg.style.filter  = 'grayscale(1)';
        document.getElementById('removeCurrentLabel').style.color = '#e04b4b';
      } else {
        currentPhotoImg.style.opacity = '1';
        currentPhotoImg.style.filter  = 'none';
      }
    });
  }

  // ══════════════════════════════════════════════
  // Upload nouvelle photo + prévisualisation
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
      // Masquer la photo actuelle si on en charge une nouvelle
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
      // Réafficher la photo actuelle si elle existe
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
  // Tags input
  // ══════════════════════════════════════════════
  const tagsInput   = document.getElementById('tagsInput');
  const tagsHidden  = document.getElementById('tagsHidden');
  const tagsWrapper = document.getElementById('tagsWrapper');
  let tags = tagsHidden.value
    ? tagsHidden.value.split(',').map(t => t.trim()).filter(Boolean)
    : [];

  function renderTags() {
    document.querySelectorAll('.tag-pill').forEach(p => p.remove());
    tags.forEach((tag, i) => {
      const pill = document.createElement('span');
      pill.className = 'tag-pill';
      pill.innerHTML = `${tag} <button type="button" data-i="${i}">×</button>`;
      tagsWrapper.insertBefore(pill, tagsInput);
    });
    tagsHidden.value = tags.join(', ');
  }

  tagsWrapper.addEventListener('click', e => {
    if (e.target.dataset.i !== undefined) {
      tags.splice(+e.target.dataset.i, 1);
      renderTags();
    } else {
      tagsInput.focus();
    }
  });

  tagsInput.addEventListener('keydown', e => {
    if ((e.key === 'Enter' || e.key === ',') && tagsInput.value.trim()) {
      e.preventDefault();
      const val = tagsInput.value.trim().replace(/,$/, '');
      if (val && !tags.includes(val)) tags.push(val);
      tagsInput.value = '';
      renderTags();
    }
    if (e.key === 'Backspace' && tagsInput.value === '' && tags.length) {
      tags.pop(); renderTags();
    }
  });

  renderTags(); // Afficher les tags existants
  </script>
</body>
</html>
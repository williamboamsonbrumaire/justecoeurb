<?php
include "./model/post-crud.php";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Nouvelle Publication</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
    /* ── Upload zone ── */
    .upload-zone {
      border: 2px dashed #D6D7E3;
      border-radius: 10px;
      padding: 40px 20px;
      text-align: center;
      cursor: pointer;
      transition: 0.3s all;
      background-color: #eff0f6;
      position: relative;
    }
    .upload-zone:hover,
    .upload-zone.drag-over {
      border-color: #2f49d1;
      background-color: rgba(47, 73, 209, 0.05);
    }
    .upload-zone input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      min-height: unset;
      background: transparent;
      border: none !important;
      box-shadow: none !important;
    }
    .upload-zone__icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      background-color: rgba(47, 73, 209, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 12px;
      color: #2f49d1;
    }
    .upload-zone__title {
      font-weight: 600;
      font-size: 14px;
      color: #171717;
      margin-bottom: 4px;
    }
    .upload-zone__subtitle {
      font-size: 12px;
      color: #b9b9b9;
    }
    .upload-zone__preview {
      display: none;
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      max-height: 200px;
    }
    .upload-zone__preview img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .upload-zone__preview-remove {
      position: absolute;
      top: 8px;
      right: 8px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: rgba(242, 100, 100, 0.9);
      border: none;
      color: #fff;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      font-size: 16px;
      line-height: 1;
    }

    /* ── Textarea ── */
    .form-textarea {
      border-radius: 8px;
      border-width: 0;
      padding: 10px 16px;
      background: #eff0f6;
      width: 100%;
      resize: vertical;
      min-height: 140px;
      font-family: inherit;
      font-size: 14px;
      color: #171717;
      border: solid transparent 2px !important;
      transition: 0.3s all;
    }
    .form-textarea::placeholder { color: #d6d7e3; }
    .form-textarea:focus {
      outline: none;
      border: rgba(134, 182, 254, 0.5) solid !important;
      box-shadow: 0 0 0 2px rgba(134, 182, 254, 0.5);
    }

    /* ── Alert messages ── */
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
    .alert-success {
      background-color: rgba(75, 222, 151, 0.12);
      color: #27a96c;
      border: 1px solid rgba(75, 222, 151, 0.3);
    }
    .alert-danger {
      background-color: rgba(242, 100, 100, 0.1);
      color: #e04b4b;
      border: 1px solid rgba(242, 100, 100, 0.25);
    }

    /* ── Section header ── */
    .section-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 28px;
    }
    .section-header__icon {
      width: 42px;
      height: 42px;
      border-radius: 10px;
      background-color: rgba(47, 73, 209, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #2f49d1;
      flex-shrink: 0;
    }
    .section-header__title {
      font-weight: 700;
      font-size: 20px;
      color: #171717;
      margin-bottom: 2px;
    }
    .section-header__subtitle {
      font-size: 13px;
      color: #b9b9b9;
      font-weight: 500;
    }

    /* ── Form group spacing ── */
    .form-group { margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }

    /* ── Character counter ── */
    .char-count {
      font-size: 11px;
      color: #b9b9b9;
      text-align: right;
      margin-top: 4px;
    }
    .char-count.warn { color: #ffb648; }
    .char-count.over  { color: #f26464; }

    /* ── Breadcrumb ── */
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #b9b9b9;
      margin-bottom: 20px;
    }
    .breadcrumb a { color: #2f49d1; }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* ── Submit button full ── */
    .btn-submit {
      width: 100%;
      min-height: 48px;
      font-size: 15px;
      font-weight: 600;
      letter-spacing: 0.3px;
      border-radius: 8px;
    }
    .btn-cancel {
      width: 100%;
      min-height: 48px;
      font-size: 15px;
      font-weight: 600;
      border-radius: 8px;
    }

    /* ── Dark mode overrides ── */
    .darkmode .upload-zone {
      border-color: #37374B;
      background-color: #222235;
    }
    .darkmode .upload-zone:hover,
    .darkmode .upload-zone.drag-over {
      border-color: #5887ff;
      background-color: rgba(88, 135, 255, 0.05);
    }
    .darkmode .upload-zone__title { color: #EFF0F6; }
    .darkmode .form-textarea {
      background-color: #222235;
      color: #D6D7E3;
    }
    .darkmode .section-header__title { color: #EFF0F6; }
    .darkmode .breadcrumb { color: #767676; }
    .darkmode .form { background-color: #161624; }
    .darkmode .form-label { color: #D6D7E3; }
    .darkmode .form-input { background-color: #222235; color: #D6D7E3; }
    .darkmode .char-count { color: #767676; }
  </style>
</head>

<body>


  <div class="layer"></div>
  <a class="skip-link sr-only" href="#skip-target">Skip to content</a>

  <div class="page-flex">
    <!-- Sidebar -->
    <?php include "./inside/aside.php"; ?>

    <div class="main-wrapper">
      <!-- Nav -->
      <?php include "./inside/nav.php"; ?>

      <!-- Main -->
      <main class="main users" id="skip-target">
        <div class="container">

          <!-- Breadcrumb -->
          <nav class="breadcrumb">
            <a href="index.php">Accueil</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
            <span>Nouvelle publication</span>
          </nav>

          <h2 class="main-title">Publications</h2>

          <div class="row">
            <!-- ── Formulaire principal ── -->
            <div class="col-lg-8">
              <div class="form">

                <!-- En-tête section -->
                <div class="section-header">
                  <div class="section-header__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="section-header__title">Nouvelle publication</div>
                    <div class="section-header__subtitle">Remplissez les informations ci-dessous</div>
                  </div>
                </div>

                <!-- Alertes -->
                <?php if ($msg_success): ?>
                  <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <?= htmlspecialchars($msg_success) ?>
                  </div>
                <?php endif; ?>
                <?php if ($msg_error): ?>
                  <div class="alert alert-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
                      <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($msg_error) ?>
                  </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form method="POST" enctype="multipart/form-data" id="articleForm">

                  <!-- Auteur -->
                  <div class="form-group">
                    <div class="form-label-wrapper">
                      <label class="form-label" for="author">
                        Auteur <span style="color:#f26464">*</span>
                      </label>
                      <input
                        type="text"
                        id="author"
                        name="author"
                        class="form-input"
                        placeholder="Nom de l'auteur"
                        maxlength="156"
                        value="<?= htmlspecialchars($_POST['author'] ?? '') ?>"
                        required
                      >
                      <span class="char-count" id="authorCount">0 / 156</span>
                    </div>
                  </div>

                  <!-- Description -->
                  <div class="form-group">
                    <div class="form-label-wrapper">
                      <label class="form-label" for="description">
                        Description <span style="color:#f26464">*</span>
                      </label>
                      <textarea
                        id="description"
                        name="description"
                        class="form-textarea"
                        placeholder="Contenu de la publication..."
                        required
                      ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                      <span class="char-count" id="descCount">0 caractères</span>
                    </div>
                  </div>

                  <!-- Lien article -->
                  <div class="form-group">
                    <div class="form-label-wrapper">
                      <label class="form-label" for="link_article">
                        Lien externe
                        <span style="color:#b9b9b9; font-weight:400; font-size:12px;">(optionnel)</span>
                      </label>
                      <input
                        type="url"
                        id="link_article"
                        name="link_article"
                        class="form-input"
                        placeholder="https://exemple.com/article"
                        maxlength="255"
                        value="<?= htmlspecialchars($_POST['link_article'] ?? '') ?>"
                      >
                    </div>
                  </div>

                  <!-- Photo -->
                  <div class="form-group">
                    <label class="form-label">
                      Photo
                      <span style="color:#b9b9b9; font-weight:400; font-size:12px;">(optionnel · max 5 Mo)</span>
                    </label>

                    <div class="upload-zone" id="uploadZone">
                      <input type="file" name="photo" id="photoInput" accept="image/*">

                      <!-- État vide -->
                      <div id="uploadPlaceholder">
                        <div class="upload-zone__icon">
                          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                               fill="none" stroke="currentColor" stroke-width="2"
                               stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                          </svg>
                        </div>
                        <p class="upload-zone__title">Cliquez ou déposez une image ici</p>
                        <p class="upload-zone__subtitle">JPG, PNG, WEBP, GIF - 5 Mo max</p>
                      </div>

                      <!-- Prévisualisation -->
                      <div class="upload-zone__preview" id="previewContainer">
                        <img src="" alt="Aperçu" id="previewImg">
                        <button type="button" class="upload-zone__preview-remove" id="removePhoto" title="Supprimer">✕</button>
                      </div>
                    </div>
                  </div>

                  <!-- Boutons -->
                  <div class="row" style="margin-top:28px;">
                    <div class="col-6">
                      <a href="articles.php" class="secondary-default-btn btn-cancel" style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
                        Annuler
                      </a>
                    </div>
                    <div class="col-6">

                      <button type="submit" name="add_article"  class="primary-default-btn btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;">
                          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Publier
                      </button> 
                    </div>
                  </div>

                </form>
              </div><!-- .form -->
            </div><!-- col-lg-8 -->

            <!-- ── Panneau latéral info ── -->
            <div class="col-lg-4">

              <!-- Conseils -->
              <div class="white-block" style="margin-bottom:20px;">
                <h3 class="white-block__title">Conseils de rédaction</h3>
                <ul style="padding-left:0; list-style:none;">
                  <li style="display:flex; gap:10px; margin-bottom:14px; font-size:13px; line-height:1.5; color:#767676;">
                    <span style="color:#2f49d1; flex-shrink:0; margin-top:1px;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2.5"
                           stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </span>
                    Rédigez une description claire et concise.
                  </li>
                  <li style="display:flex; gap:10px; margin-bottom:14px; font-size:13px; line-height:1.5; color:#767676;">
                    <span style="color:#2f49d1; flex-shrink:0; margin-top:1px;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2.5"
                           stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </span>
                    Utilisez une image de bonne qualité (ratio 16:9 recommandé).
                  </li>
                  <li style="display:flex; gap:10px; font-size:13px; line-height:1.5; color:#767676;">
                    <span style="color:#2f49d1; flex-shrink:0; margin-top:1px;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                           fill="none" stroke="currentColor" stroke-width="2.5"
                           stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </span>
                    Le lien externe est optionnel ; laissez vide si non applicable.
                  </li>
                </ul>
              </div>

              <!-- Champs obligatoires -->
              <div class="white-block">
                <h3 class="white-block__title">Récapitulatif des champs</h3>
                <div style="font-size:13px; color:#767676; line-height:2;">
                  <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eeeeee; padding:6px 0;">
                    <span>Auteur</span>
                    <span class="badge-trashed" style="width:auto; padding:2px 10px;">Requis</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eeeeee; padding:6px 0;">
                    <span>Description</span>
                    <span class="badge-trashed" style="width:auto; padding:2px 10px;">Requis</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eeeeee; padding:6px 0;">
                    <span>Photo</span>
                    <span class="badge-success" style="width:auto; padding:2px 10px;">Optionnel</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:6px 0;">
                    <span>Lien externe</span>
                    <span class="badge-success" style="width:auto; padding:2px 10px;">Optionnel</span>
                  </div>
                </div>
              </div>

            </div><!-- col-lg-4 -->
          </div><!-- .row -->

        </div><!-- .container -->
      </main>

      <!-- Footer -->
      <?php include "./inside/footer.php"; ?>
    </div><!-- .main-wrapper -->
  </div><!-- .page-flex -->
<script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
<script>
  // ── Compteurs de caractères ──────────────────────────────────────────────
  const authorInput = document.getElementById('author');
  const authorCount = document.getElementById('authorCount');
  const descInput   = document.getElementById('description');
  const descCount   = document.getElementById('descCount');

  function updateAuthorCount() {
    const len = authorInput.value.length;
    authorCount.textContent = len + ' / 156';
    authorCount.className = 'char-count' + (len > 140 ? (len >= 156 ? ' over' : ' warn') : '');
  }
  function updateDescCount() {
    const len = descInput.value.length;
    descCount.textContent = len + ' caractères';
    descCount.className = 'char-count' + (len > 800 ? ' warn' : '');
  }
  authorInput.addEventListener('input', updateAuthorCount);
  descInput.addEventListener('input', updateDescCount);
  updateAuthorCount();
  updateDescCount();

  // ── Prévisualisation image ───────────────────────────────────────────────
  const photoInput       = document.getElementById('photoInput');
  const uploadZone       = document.getElementById('uploadZone');
  const uploadPlaceholder = document.getElementById('uploadPlaceholder');
  const previewContainer = document.getElementById('previewContainer');
  const previewImg       = document.getElementById('previewImg');
  const removeBtn        = document.getElementById('removePhoto');

  photoInput.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
      previewImg.src = e.target.result;
      uploadPlaceholder.style.display = 'none';
      previewContainer.style.display = 'block';
    };
    reader.readAsDataURL(file);
  });

  removeBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    photoInput.value = '';
    previewImg.src = '';
    previewContainer.style.display = 'none';
    uploadPlaceholder.style.display = 'block';
  });

  // ── Drag & drop ──────────────────────────────────────────────────────────
  uploadZone.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
  uploadZone.addEventListener('dragleave', ()  => uploadZone.classList.remove('drag-over'));
  uploadZone.addEventListener('drop', function (e) {
    e.preventDefault();
    uploadZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
      const dt = new DataTransfer();
      dt.items.add(file);
      photoInput.files = dt.files;
      photoInput.dispatchEvent(new Event('change'));
    }
  });
</script>
</body>
</html>
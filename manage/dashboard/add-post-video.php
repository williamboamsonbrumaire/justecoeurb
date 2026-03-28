<?php include "./model/post-video-crud.php";
global $msg_success ;
global $msg_error   ;
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Nouvelle Vidéo</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
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
      background-color: rgba(242, 100, 100, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #f26464;
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
    .char-count.over { color: #f26464; }

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

    /* ── Buttons ── */
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

    /* ── YouTube preview ── */
    .yt-preview-wrapper {
      display: none;
      margin-top: 12px;
      border-radius: 10px;
      overflow: hidden;
      background: #000;
      position: relative;
      aspect-ratio: 16 / 9;
    }
    .yt-preview-wrapper iframe {
      width: 100%;
      height: 100%;
      border: none;
      display: block;
    }
    .yt-preview-wrapper.visible { display: block; }

    .yt-url-status {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      margin-top: 6px;
      font-weight: 500;
      min-height: 18px;
    }
    .yt-url-status.valid   { color: #27a96c; }
    .yt-url-status.invalid { color: #e04b4b; }

    /* ── Input with icon ── */
    .input-icon-wrapper {
      position: relative;
    }
    .input-icon-wrapper .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #f26464;
      pointer-events: none;
    }
    .input-icon-wrapper .form-input {
      padding-left: 42px;
    }

    /* ── Dark mode overrides ── */
    .darkmode .section-header__title  { color: #EFF0F6; }
    .darkmode .breadcrumb             { color: #767676; }
    .darkmode .form                   { background-color: #161624; }
    .darkmode .form-label             { color: #D6D7E3; }
    .darkmode .form-input             { background-color: #222235; color: #D6D7E3; }
    .darkmode .char-count             { color: #767676; }
    .darkmode .yt-preview-wrapper     { background: #111; }
    .darkmode .section-header__icon   { background-color: rgba(242,100,100,0.15); }
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
            <a href="videos.php">Vidéos</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
            <span>Nouvelle vidéo</span>
          </nav>

          <h2 class="main-title">Vidéos YouTube</h2>

          <div class="row">
            <!-- ── Formulaire principal ── -->
            <div class="col-lg-8">
              <div class="form">

                <!-- En-tête -->
                <div class="section-header">
                  <div class="section-header__icon">
                    <!-- Icône YouTube -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                         fill="currentColor">
                      <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="section-header__title">Nouvelle vidéo</div>
                    <div class="section-header__subtitle">Ajoutez une vidéo YouTube à votre galerie</div>
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
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="12" y1="8" x2="12" y2="12"/>
                      <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <?= htmlspecialchars($msg_error) ?>
                  </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form method="POST" id="videoForm">

                  <!-- Titre -->
                  <div class="form-group">
                    <div class="form-label-wrapper">
                      <label class="form-label" for="title_video">
                        Titre de la vidéo <span style="color:#f26464">*</span>
                      </label>
                      <input
                        type="text"
                        id="title_video"
                        name="title_video"
                        class="form-input"
                        placeholder="Ex : Présentation de nos activités 2024"
                        maxlength="156"
                        value="<?= htmlspecialchars($_POST['title_video'] ?? '') ?>"
                        required
                      >
                      <span class="char-count" id="titleCount">0 / 156</span>
                    </div>
                  </div>

                  <!-- Lien YouTube -->
                  <div class="form-group">
                    <div class="form-label-wrapper">
                      <label class="form-label" for="link_youtube">
                        Lien YouTube <span style="color:#f26464">*</span>
                      </label>
                      <div class="input-icon-wrapper">
                        <span class="input-icon">
                          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                               fill="currentColor">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                          </svg>
                        </span>
                        <input
                          type="url"
                          id="link_youtube"
                          name="link_youtube"
                          class="form-input"
                          placeholder="https://www.youtube.com/watch?v=..."
                          maxlength="256"
                          value="<?= htmlspecialchars($_POST['link_youtube'] ?? '') ?>"
                          required
                        >
                      </div>
                      <!-- Statut validation URL -->
                      <div class="yt-url-status" id="ytStatus"></div>
                    </div>

                    <!-- Prévisualisation YouTube -->
                    <div class="yt-preview-wrapper" id="ytPreview">
                      <iframe
                        id="ytIframe"
                        src=""
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                      ></iframe>
                    </div>
                  </div>

                  <!-- Boutons -->
                  <div class="row" style="margin-top:28px;">
                    <div class="col-6">
                      <a href="videos.php"
                         class="secondary-default-btn btn-cancel"
                         style="text-decoration:none; display:flex; align-items:center; justify-content:center;">
                        Annuler
                      </a>
                    </div>
                    <div class="col-6">
                      <button type="submit" name="add_video" class="primary-default-btn btn-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;">
                          <line x1="12" y1="5" x2="12" y2="19"/>
                          <line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        Ajouter la vidéo
                      </button>
                    </div>
                  </div>

                </form>
              </div><!-- .form -->
            </div><!-- col-lg-8 -->

            <!-- ── Panneau latéral ── -->
            <div class="col-lg-4">

              <!-- Comment trouver le lien -->
              <div class="white-block" style="margin-bottom:20px;">
                <h3 class="white-block__title">Comment trouver le lien ?</h3>
                <ul style="padding-left:0; list-style:none;">
                  <li style="display:flex; gap:10px; margin-bottom:16px; font-size:13px; line-height:1.6; color:#767676;">
                    <span style="background:rgba(242,100,100,0.1); color:#f26464; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; margin-top:1px;">1</span>
                    Ouvrez la vidéo sur <strong style="color:#171717;">YouTube</strong>
                  </li>
                  <li style="display:flex; gap:10px; margin-bottom:16px; font-size:13px; line-height:1.6; color:#767676;">
                    <span style="background:rgba(242,100,100,0.1); color:#f26464; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; margin-top:1px;">2</span>
                    Copiez l'URL dans la barre d'adresse du navigateur
                  </li>
                  <li style="display:flex; gap:10px; font-size:13px; line-height:1.6; color:#767676;">
                    <span style="background:rgba(242,100,100,0.1); color:#f26464; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; flex-shrink:0; margin-top:1px;">3</span>
                    Collez-la dans le champ <strong style="color:#171717;">Lien YouTube</strong> ci-contre
                  </li>
                </ul>
              </div>

              <!-- Formats acceptés -->
              <div class="white-block" style="margin-bottom:20px;">
                <h3 class="white-block__title">Formats acceptés</h3>
                <div style="font-size:12px; color:#767676; line-height:1;">
                  <div style="padding:8px 0; border-bottom:1px solid #eeeeee; font-family:monospace; font-size:11px; word-break:break-all;">
                    youtube.com/watch?v=…
                  </div>
                  <div style="padding:8px 0; border-bottom:1px solid #eeeeee; font-family:monospace; font-size:11px;">
                    youtu.be/…
                  </div>
                  <div style="padding:8px 0; border-bottom:1px solid #eeeeee; font-family:monospace; font-size:11px;">
                    youtube.com/embed/…
                  </div>
                  <div style="padding:8px 0; font-family:monospace; font-size:11px;">
                    youtube.com/shorts/…
                  </div>
                </div>
              </div>

              <!-- Récapitulatif -->
              <div class="white-block">
                <h3 class="white-block__title">Récapitulatif des champs</h3>
                <div style="font-size:13px; color:#767676;">
                  <div style="display:flex; justify-content:space-between; border-bottom:1px solid #eeeeee; padding:8px 0;">
                    <span>Titre</span>
                    <span class="badge-trashed" style="width:auto; padding:2px 10px;">Requis</span>
                  </div>
                  <div style="display:flex; justify-content:space-between; padding:8px 0;">
                    <span>Lien YouTube</span>
                    <span class="badge-trashed" style="width:auto; padding:2px 10px;">Requis</span>
                  </div>
                </div>
              </div>

            </div><!-- col-lg-4 -->
          </div><!-- .row -->

        </div><!-- .container -->
      </main>

      <?php include "./inside/footer.php"; ?>
    </div><!-- .main-wrapper -->
  </div><!-- .page-flex -->

<script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
<script>
  // ── Compteur titre ────────────────────────────────────────────────────────
  const titleInput = document.getElementById('title_video');
  const titleCount = document.getElementById('titleCount');

  function updateTitleCount() {
    const len = titleInput.value.length;
    titleCount.textContent = len + ' / 156';
    titleCount.className = 'char-count' + (len > 130 ? (len >= 156 ? ' over' : ' warn') : '');
  }
  titleInput.addEventListener('input', updateTitleCount);
  updateTitleCount();

  // ── Extraction ID YouTube ─────────────────────────────────────────────────
  function extractYoutubeId(url) {
    const match = url.match(
      /(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/
    );
    return match ? match[1] : null;
  }

  // ── Prévisualisation YouTube ──────────────────────────────────────────────
  const ytInput   = document.getElementById('link_youtube');
  const ytPreview = document.getElementById('ytPreview');
  const ytIframe  = document.getElementById('ytIframe');
  const ytStatus  = document.getElementById('ytStatus');

  let debounceTimer;

  function updatePreview() {
    const url = ytInput.value.trim();
    if (!url) {
      ytPreview.classList.remove('visible');
      ytStatus.innerHTML = '';
      ytStatus.className = 'yt-url-status';
      return;
    }

    const id = extractYoutubeId(url);

    if (id) {
      ytIframe.src = 'https://www.youtube.com/embed/' + id;
      ytPreview.classList.add('visible');
      ytStatus.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Lien valide — aperçu disponible`;
      ytStatus.className = 'yt-url-status valid';
    } else {
      ytIframe.src = '';
      ytPreview.classList.remove('visible');
      ytStatus.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        Lien YouTube non reconnu`;
      ytStatus.className = 'yt-url-status invalid';
    }
  }

  ytInput.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(updatePreview, 400); // délai pour ne pas spammer
  });

  // Charger la prévisualisation si la valeur est déjà là (après erreur de soumission)
  if (ytInput.value) updatePreview();
</script>
</body>
</html>
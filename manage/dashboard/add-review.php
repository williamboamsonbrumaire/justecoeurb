<?php
/**
 * add-review.php — Ajouter un nouveau témoignage
 */
require_once __DIR__ . '/model/reviews-crud.php';

// Initialisation d'un tableau vide pour le formulaire
$review = [
    'nom'          => '',
    'role'         => '',
    'organisation' => '',
    'quote'        => '',
    'photo'        => '',
    'ordre'        => 0,
    'statut'       => 'actif'
];

// Si le formulaire est soumis et qu'il y a des erreurs, on récupère les données saisies
if (!empty($_POST)) {
    $review = array_merge($review, $_POST);
}

// Après soumission réussie via le controller (reviews-crud.php)
if (isset($msg_success) && $msg_success && empty($msg_error)) {
    $_SESSION['flash_success'] = $msg_success;
    header('Location: reviews.php');
    exit;
}

$isEdit    = false;
$pageTitle = "Ajouter un témoignage";
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
    /* Styles identiques à votre version edit pour garder la cohérence */
    .rv-form-layout { display: grid; grid-template-columns: 1fr 290px; gap: 20px; align-items: start; }
    @media (max-width: 991px) { .rv-form-layout { grid-template-columns: 1fr; } }

    .alert { padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .alert-danger { background: rgba(242,100,100,.10); color: #e04b4b; border: 1px solid rgba(242,100,100,.25); }

    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #b9b9b9; margin-bottom: 20px; }
    .breadcrumb a { color: #1a1a2e; text-decoration: none; }
    .breadcrumb svg { width: 14px; height: 14px; }

    .section-header { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; }
    .section-header__icon { width: 42px; height: 42px; border-radius: 10px; background: rgba(26,26,46,.08); color: #1a1a2e; display: flex; align-items: center; justify-content: center; }
    .section-header__title { font-weight: 700; font-size: 19px; color: #171717; }

    .form-group { margin-bottom: 20px; }
    .form-label { font-weight: 600; font-size: 13px; color: #171717; display: block; margin-bottom: 7px; }
    .form-label .req { color: #f26464; }
    .form-input, .form-textarea, .form-select { width: 100%; border-radius: 8px; background: #eff0f6; border: 2px solid transparent !important; padding: 10px 14px; font-size: 14px; color: #171717; transition: .2s; }
    .form-input:focus, .form-textarea:focus { outline: none; border-color: rgba(134,182,254,.6) !important; box-shadow: 0 0 0 3px rgba(134,182,254,.2); }

    .quote-preview { background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%); border-radius: 12px; padding: 20px; margin-top: 14px; position: relative; }
    .quote-preview__text { font-size: 12.5px; color: rgba(255,255,255,.75); font-style: italic; line-height: 1.7; }

    .statut-toggle { display: flex; gap: 8px; }
    .statut-option { flex: 1; }
    .statut-option input { display: none; }
    .statut-option label { display: flex; align-items: center; justify-content: center; gap: 7px; padding: 10px; border-radius: 8px; cursor: pointer; font-size: 13px; border: 2px solid #eeeeee; color: #b9b9b9; width: 100%; }
    .statut-option input:checked + label.actif { border-color: #4bde97; background: rgba(75,222,151,.08); color: #27a96c; }

    .sidebar-card { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 2px 12px rgba(160,163,189,.08); margin-bottom: 16px; }
    .btn-save { width: 100%; min-height: 46px; font-size: 14px; font-weight: 700; border-radius: 8px; background: #1a1a2e; color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; }

    .upload-zone { border: 2px dashed #D6D7E3; border-radius: 10px; padding: 24px 14px; text-align: center; cursor: pointer; background: #eff0f6; position: relative; }
    .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .upload-preview img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto; display: block; }
  </style>
</head>
<body>
  <div class="page-flex">
    <?php include "./inside/aside.php"; ?>
    <div class="main-wrapper">
      <?php include "./inside/nav.php"; ?>

      <main class="main">
        <div class="container">
          <nav class="breadcrumb">
            <a href="index.php">Accueil</a> > <a href="reviews.php">Témoignages</a> > <span>Ajouter</span>
          </nav>

          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <h2 class="main-title"><?= $pageTitle ?></h2>
            <a href="reviews.php" class="secondary-default-btn">← Retour</a>
          </div>

          <?php if (isset($msg_error) && $msg_error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($msg_error) ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" id="reviewForm">
            <div class="rv-form-layout">
              <div class="form">
                <div class="section-header">
                   <div class="section-header__title">Nouveau Témoignage</div>
                </div>

                <div class="form-group">
                  <label class="form-label" for="nom">Nom complet <span class="req">*</span></label>
                  <input type="text" id="nom" name="nom" class="form-input" value="<?= htmlspecialchars($review['nom']) ?>" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="role">Titre / Poste <span class="req">*</span></label>
                  <input type="text" id="role" name="role" class="form-input" value="<?= htmlspecialchars($review['role']) ?>" required>
                </div>

                <div class="form-group">
                  <label class="form-label" for="organisation">Organisation <span class="opt">- optionnel</span></label>
                  <input type="text" id="organisation" name="organisation" class="form-input" value="<?= htmlspecialchars($review['organisation']) ?>">
                </div>

                <div class="form-group">
                  <label class="form-label" for="quote">Témoignage <span class="req">*</span></label>
                  <textarea id="quote" name="quote" class="form-textarea" required><?= htmlspecialchars($review['quote']) ?></textarea>
                  
                  <div class="quote-preview" id="quotePreview" style="display:none">
                    <p class="quote-preview__text" id="previewQuoteText"></p>
                    <div style="color:white; font-size:12px; margin-top:10px;">
                      — <strong id="previewName"></strong> <span id="previewRole"></span>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <div class="sidebar-card">
                  <div class="form-group">
                    <label class="form-label">Statut</label>
                    <div class="statut-toggle">
                      <div class="statut-option">
                        <input type="radio" id="st_inactif" name="statut" value="inactif" <?= $review['statut'] === 'inactif' ? 'checked' : '' ?>>
                        <label for="st_inactif">Inactif</label>
                      </div>
                      <div class="statut-option">
                        <input type="radio" id="st_actif" name="statut" value="actif" <?= $review['statut'] === 'actif' ? 'checked' : '' ?>>
                        <label for="st_actif" class="actif">Actif</label>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="ordre" class="form-input" value="<?= (int)$review['ordre'] ?>">
                  </div>

                  <button type="submit" name="add_review" class="btn-save">
                    Ajouter le témoignage
                  </button>
                </div>

                <div class="sidebar-card">
                   <label class="form-label">Photo du témoin</label>
                   <div class="upload-zone">
                      <input type="file" name="photo" id="photoInput" accept="image/*">
                      <div id="previewContainer" style="display:none;">
                         <img src="" id="previewImg">
                      </div>
                      <p id="uploadTxt">Cliquer ou glisser une photo</p>
                   </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </main>
    </div>
  </div>

  <script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>

  <script>
    // Aperçu live (simplifié)
    const quoteIn = document.getElementById('quote');
    const nomIn   = document.getElementById('nom');
    const roleIn  = document.getElementById('role');
    const prev    = document.getElementById('quotePreview');

    function update() {
      if(quoteIn.value.trim()){
        prev.style.display = 'block';
        document.getElementById('previewQuoteText').textContent = quoteIn.value;
        document.getElementById('previewName').textContent = nomIn.value || "Nom";
        document.getElementById('previewRole').textContent = roleIn.value ? ", " + roleIn.value : "";
      } else { prev.style.display = 'none'; }
    }
    quoteIn.oninput = update; nomIn.oninput = update; roleIn.oninput = update;

    // Photo preview
    document.getElementById('photoInput').onchange = function(e) {
      const reader = new FileReader();
      reader.onload = function(ev) {
        document.getElementById('previewImg').src = ev.target.result;
        document.getElementById('previewContainer').style.display = 'block';
        document.getElementById('uploadTxt').style.display = 'none';
      };
      reader.readAsDataURL(this.files[0]);
    };
  </script>
</body>
</html>
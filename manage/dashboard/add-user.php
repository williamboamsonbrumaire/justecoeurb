<?php
require_once "./model/user-crud.php";

// On initialise un tableau vide pour ne pas casser l'affichage des values
$user = [
    'name_user' => '',
    'lastname_user' => '',
    'email_user' => '',
    'role_user' => 'viewer',
    'statut_user' => 'actif',
    'photo_user' => ''
];

$errors = [];

// Si soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    // Validation basique
    if (empty(trim($_POST['name_user'])))      $errors[] = 'Le prénom est requis.';
    if (empty(trim($_POST['lastname_user'])))  $errors[] = 'Le nom est requis.';
    if (empty(trim($_POST['email_user'])))     $errors[] = 'L\'email est requis.';
    elseif (!filter_var(trim($_POST['email_user']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'email n\'est pas valide.';
    }
    
    // Pour un ajout, le mot de passe est obligatoire
    if (empty($_POST['password_user'])) {
        $errors[] = 'Le mot de passe est requis pour un nouvel utilisateur.';
    } elseif (strlen($_POST['password_user']) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }

    if (empty($errors)) {
        // Le traitement se fait dans user-crud.php via l'action du formulaire
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Ajouter un utilisateur</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
    /* ── Styles conservés à l'identique pour garder la cohérence visuelle ── */
    .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #b9b9b9; margin-bottom: 20px; }
    .breadcrumb a { color: #2f49d1; text-decoration: none; }
    .breadcrumb svg { width: 14px; height: 14px; }
    .form-card { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(160,163,189,.10); overflow: hidden; max-width: 760px; }
    .form-card__header { display: flex; align-items: center; gap: 14px; padding: 20px 24px; border-bottom: 1px solid #eeeeee; }
    .form-card__icon { width: 42px; height: 42px; border-radius: 10px; background: rgba(47,73,209,.1); color: #2f49d1; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .form-card__title  { font-weight: 700; font-size: 16px; color: #171717; }
    .form-card__sub    { font-size: 12px; color: #b9b9b9; margin-top: 2px; }
    .form-card__body   { padding: 28px 24px; }
    .form-card__footer { padding: 16px 24px; border-top: 1px solid #eeeeee; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
    .avatar-section { display: flex; align-items: center; gap: 20px; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid #f3f3f3; }
    .avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #eff0f6; flex-shrink: 0; }
    .avatar-placeholder { width: 80px; height: 80px; border-radius: 50%; flex-shrink: 0; background: #eff0f6; display: flex; align-items: center; justify-content: center; color: #b9b9b9; border: 3px solid #eff0f6; }
    .avatar-info { flex: 1; }
    .avatar-info p { font-size: 13px; color: #767676; margin: 4px 0 12px; }
    .upload-btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; background: #eff0f6; color: #2f49d1; cursor: pointer; border: 1.5px dashed #d6d7e3; transition: .2s all; }
    .upload-btn:hover { background: rgba(47,73,209,.08); border-color: #2f49d1; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .form-grid .full { grid-column: 1 / -1; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label { font-size: 12px; font-weight: 700; color: #767676; text-transform: uppercase; letter-spacing: .5px; }
    .form-label span { color: #f26464; margin-left: 2px; }
    .form-input, .form-select { height: 42px; border: 1.5px solid #eeeeee; border-radius: 9px; padding: 0 14px; font-size: 13px; color: #171717; background: #fff; outline: none; transition: .2s all; width: 100%; box-sizing: border-box; }
    .form-input:focus, .form-select:focus { border-color: #2f49d1; box-shadow: 0 0 0 3px rgba(47,73,209,.08); }
    .pwd-wrap { position: relative; }
    .pwd-wrap .form-input { padding-right: 44px; }
    .pwd-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #b9b9b9; padding: 0; display: flex; }
    .alert-error { background: #fff5f5; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: #c94040; }
    .form-section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #b9b9b9; display: flex; align-items: center; gap: 10px; grid-column: 1 / -1; margin-top: 6px; }
    .form-section-label::after { content: ''; flex: 1; height: 1px; background: #eeeeee; }
    .btn-cancel { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: 9px; font-size: 14px; font-weight: 600; background: #eff0f6; color: #767676; text-decoration: none; border: none; cursor: pointer; }
    
    /* Dark mode */
    .darkmode .form-card { background: #222235; }
    .darkmode .form-input, .darkmode .form-select { background: #1a1a2e; border-color: #37374B; color: #EFF0F6; }
  </style>
</head>

<body>
  <div class="page-flex">
    <?php include "./inside/aside.php"; ?>

    <div class="main-wrapper">
      <?php include "./inside/nav.php"; ?>

      <main class="main users" id="skip-target">
        <div class="container">

          <nav class="breadcrumb">
            <a href="index.php">Accueil</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="users.php">Utilisateurs</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span>Ajouter</span>
          </nav>

          <div style="margin-bottom:24px;">
            <h2 class="main-title">Ajouter un utilisateur</h2>
          </div>

          <?php if (!empty($errors)): ?>
            <div class="alert-error">
              <strong>Erreurs de validation :</strong>
              <ul>
                <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="form-card">
            <div class="form-card__header">
              <div class="form-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="17" y1="11" x2="23" y2="11"/></svg>
              </div>
              <div>
                <div class="form-card__title">Nouvel utilisateur</div>
                <div class="form-card__sub">Remplissez les informations pour créer un accès</div>
              </div>
            </div>

            <form method="POST" action="./model/user-crud.php" enctype="multipart/form-data">
              <div class="form-card__body">

                <div class="avatar-section">
                    <div class="avatar-placeholder" id="avatarInitials">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <img id="avatarPreview" class="avatar-preview" src="" alt="" style="display:none;">

                  <div class="avatar-info">
                    <div class="avatar-name">Photo de profil</div>
                    <p>Format JPG, PNG ou SVG conseillé.</p>
                    <label class="upload-btn" for="photo_user">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                      Choisir une image
                    </label>
                    <input type="file" id="photo_user" name="photo_user" accept="image/*" style="display:none;">
                  </div>
                </div>

                <div class="form-grid">
                  <div class="form-section-label">Identité</div>
                  
                  <div class="form-group">
                    <label class="form-label" for="name_user">Prénom <span>*</span></label>
                    <input type="text" id="name_user" name="name_user" class="form-input" placeholder="Jean" required>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="lastname_user">Nom <span>*</span></label>
                    <input type="text" id="lastname_user" name="lastname_user" class="form-input" placeholder="Dupont" required>
                  </div>

                  <div class="form-group full">
                    <label class="form-label" for="email_user">Adresse email <span>*</span></label>
                    <input type="email" id="email_user" name="email_user" class="form-input" placeholder="exemple@email.com" required>
                  </div>

                  <div class="form-section-label">Accès &amp; permissions</div>

                  <div class="form-group">
                    <label class="form-label" for="role_user">Rôle</label>
                    <select id="role_user" name="role_user" class="form-select">
                      <option value="admin">Admin</option>
                      <option value="editor">Éditeur</option>
                      <option value="viewer" selected>Lecteur</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="statut_user">Statut</label>
                    <select id="statut_user" name="statut_user" class="form-select statut-select">
                      <option value="actif" selected>Actif</option>
                      <option value="inactif">Inactif</option>
                    </select>
                  </div>

                  <div class="form-section-label">Sécurité</div>

                  <div class="form-group full">
                    <label class="form-label" for="password_user">Mot de passe <span>*</span></label>
                    <div class="pwd-wrap">
                      <input type="password" id="password_user" name="password_user" class="form-input" placeholder="Minimum 6 caractères" required minlength="6">
                      <button type="button" class="pwd-toggle" id="togglePwd"><svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-card__footer">
                <a href="users.php" class="btn-cancel">Annuler</a>
                <button type="submit" name="add_user" class="primary-default-btn" style="font-size:14px; padding:9px 20px;">
                  Créer l'utilisateur
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>

      <?php include "./inside/footer.php"; ?>

      <script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
    </div>
  </div>

  <script>
    // Preview avatar
    const photoInput = document.getElementById('photo_user');
    const avatarImg = document.getElementById('avatarPreview');
    const avatarInit = document.getElementById('avatarInitials');
    photoInput?.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = ev => {
        avatarImg.src = ev.target.result;
        avatarImg.style.display = 'block';
        if (avatarInit) avatarInit.style.display = 'none';
      };
      reader.readAsDataURL(file);
    });

    // Toggle password
    const toggleBtn = document.getElementById('togglePwd');
    const pwdInput = document.getElementById('password_user');
    let visible = false;
    toggleBtn?.addEventListener('click', () => {
      visible = !visible;
      pwdInput.type = visible ? 'text' : 'password';
    });
  </script>
</body>
</html>
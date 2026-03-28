<?php
require_once "./model/user-crud.php";

$id   = (int) ($_GET['id'] ?? 0);
$user = $id ? getUserById($conDB, $id) : null;

if (!$user) {
    header('Location: users.php?error=notfound');
    exit;
}

$errors = [];

// Si soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    // Validation basique
    if (empty(trim($_POST['name_user'])))     $errors[] = 'Le prénom est requis.';
    if (empty(trim($_POST['lastname_user']))) $errors[] = 'Le nom est requis.';
    if (empty(trim($_POST['email_user'])))    $errors[] = 'L\'email est requis.';
    elseif (!filter_var(trim($_POST['email_user']), FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L\'email n\'est pas valide.';
    }
    if (!empty($_POST['password_user']) && strlen($_POST['password_user']) < 6) {
        $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
    }

    if (empty($errors)) {
        // Traitement dans user-crud.php (appelé via include donc $_POST est transmis)
        // La redirection se fait dans user-crud.php
        // → L'action du formulaire pointe vers user-crud.php directement
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Modifier l'utilisateur</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
    /* ── Breadcrumb ── */
    .breadcrumb {
      display: flex; align-items: center; gap: 6px;
      font-size: 13px; color: #b9b9b9; margin-bottom: 20px;
    }
    .breadcrumb a { color: #2f49d1; text-decoration: none; }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* ── Card form ── */
    .form-card {
      background: #fff; border-radius: 14px;
      box-shadow: 0 2px 12px rgba(160,163,189,.10);
      overflow: hidden; max-width: 760px;
    }
    .form-card__header {
      display: flex; align-items: center; gap: 14px;
      padding: 20px 24px; border-bottom: 1px solid #eeeeee;
    }
    .form-card__icon {
      width: 42px; height: 42px; border-radius: 10px;
      background: rgba(47,73,209,.1); color: #2f49d1;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .form-card__title  { font-weight: 700; font-size: 16px; color: #171717; }
    .form-card__sub    { font-size: 12px; color: #b9b9b9; margin-top: 2px; }
    .form-card__body   { padding: 28px 24px; }
    .form-card__footer {
      padding: 16px 24px; border-top: 1px solid #eeeeee;
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
      flex-wrap: wrap;
    }

    /* ── Avatar preview ── */
    .avatar-section {
      display: flex; align-items: center; gap: 20px; margin-bottom: 28px;
      padding-bottom: 24px; border-bottom: 1px solid #f3f3f3;
    }
    .avatar-preview {
      width: 80px; height: 80px; border-radius: 50%; object-fit: cover;
      border: 3px solid #eff0f6; flex-shrink: 0;
    }
    .avatar-placeholder {
      width: 80px; height: 80px; border-radius: 50%; flex-shrink: 0;
      background: linear-gradient(135deg, #2f49d1 0%, #5877f2 100%);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 700; font-size: 22px; letter-spacing: .5px;
      border: 3px solid #eff0f6;
    }
    .avatar-info { flex: 1; }
    .avatar-info p { font-size: 13px; color: #767676; margin: 4px 0 12px; }
    .avatar-name   { font-weight: 700; font-size: 17px; color: #171717; }
    .upload-btn {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 8px 14px; border-radius: 8px; font-size: 13px; font-weight: 600;
      background: #eff0f6; color: #2f49d1; cursor: pointer;
      border: 1.5px dashed #d6d7e3; transition: .2s all;
    }
    .upload-btn:hover { background: rgba(47,73,209,.08); border-color: #2f49d1; }
    .upload-btn svg   { width: 15px; height: 15px; }

    /* ── Form grid ── */
    .form-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 18px;
    }
    .form-grid .full { grid-column: 1 / -1; }
    @media (max-width: 575px) { .form-grid { grid-template-columns: 1fr; } }

    /* ── Form group ── */
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label {
      font-size: 12px; font-weight: 700; color: #767676;
      text-transform: uppercase; letter-spacing: .5px;
    }
    .form-label span { color: #f26464; margin-left: 2px; }
    .form-input, .form-select {
      height: 42px; border: 1.5px solid #eeeeee; border-radius: 9px;
      padding: 0 14px; font-size: 13px; color: #171717;
      background: #fff; outline: none; transition: .2s all;
      width: 100%; box-sizing: border-box;
    }
    .form-input:focus, .form-select:focus {
      border-color: #2f49d1; box-shadow: 0 0 0 3px rgba(47,73,209,.08);
    }
    .form-input.error { border-color: #f26464; }
    .form-input::placeholder { color: #d6d7e3; }
    .form-select { cursor: pointer; }
    .form-hint { font-size: 11px; color: #b9b9b9; margin-top: 2px; }

    /* ── Password wrapper ── */
    .pwd-wrap { position: relative; }
    .pwd-wrap .form-input { padding-right: 44px; }
    .pwd-toggle {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: #b9b9b9; padding: 0; display: flex;
    }
    .pwd-toggle svg { width: 16px; height: 16px; }
    .pwd-toggle:hover { color: #2f49d1; }

    /* ── Errors ── */
    .alert-error {
      background: #fff5f5; border: 1px solid #fecaca;
      border-radius: 10px; padding: 12px 16px;
      margin-bottom: 20px; font-size: 13px; color: #c94040;
    }
    .alert-error ul { margin: 6px 0 0 16px; padding: 0; }
    .alert-error li { margin-bottom: 2px; }

    /* ── Statut select colors ── */
    select.statut-select option[value="actif"]    { color: #0d7a55; }
    select.statut-select option[value="inactif"]  { color: #c94040; }
    select.statut-select option[value="suspendu"] { color: #b37d00; }

    /* ── Section divider ── */
    .form-section-label {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .8px; color: #b9b9b9;
      display: flex; align-items: center; gap: 10px;
      grid-column: 1 / -1; margin-top: 6px;
    }
    .form-section-label::after {
      content: ''; flex: 1; height: 1px; background: #eeeeee;
    }

    /* ── Buttons ── */
    .btn-cancel {
      display: inline-flex; align-items: center; gap: 7px;
      padding: 9px 18px; border-radius: 9px; font-size: 14px; font-weight: 600;
      background: #eff0f6; color: #767676; text-decoration: none; transition: .2s all;
      border: none; cursor: pointer;
    }
    .btn-cancel:hover { background: #e0e2f0; color: #444; }
    .btn-cancel svg   { width: 15px; height: 15px; }

    /* ── Dark mode ── */
    .darkmode .form-card        { background: #222235; box-shadow: none; }
    .darkmode .form-card__header,
    .darkmode .form-card__footer { border-color: #37374B; }
    .darkmode .form-card__title  { color: #EFF0F6; }
    .darkmode .avatar-name       { color: #EFF0F6; }
    .darkmode .form-section-label::after { background: #37374B; }
    .darkmode .form-input, .darkmode .form-select {
      background: #1a1a2e; border-color: #37374B; color: #EFF0F6;
    }
    .darkmode .form-input:focus, .darkmode .form-select:focus {
      border-color: #2f49d1; box-shadow: 0 0 0 3px rgba(47,73,209,.12);
    }
    .darkmode .avatar-section    { border-color: #37374B; }
    .darkmode .avatar-preview,
    .darkmode .avatar-placeholder { border-color: #37374B; }
    .darkmode .upload-btn        { background: #2a2a40; border-color: #37374B; }
    .darkmode .btn-cancel        { background: #2a2a40; color: #D6D7E3; }
    .darkmode .alert-error       { background: #2e1f1f; border-color: #7a3a3a; }
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
            <a href="users.php">Utilisateurs</a>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
            <span>Modifier</span>
          </nav>

          <!-- Title -->
          <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <h2 class="main-title" style="margin-bottom:0;">Modifier l'utilisateur</h2>
          </div>

          <?php if (!empty($errors)): ?>
            <div class="alert-error">
              <strong>Veuillez corriger les erreurs suivantes :</strong>
              <ul>
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <?php
            $initials = strtoupper(
              mb_substr($user['name_user'], 0, 1) .
              mb_substr($user['lastname_user'], 0, 1)
            );
          ?>

          <div class="form-card">
            <!-- Header card -->
            <div class="form-card__header">
              <div class="form-card__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
              </div>
              <div>
                <div class="form-card__title">Informations de l'utilisateur</div>
                <div class="form-card__sub">
                  Modification du compte #<?= $user['id_user'] ?> —
                  créé le <?= date('d M Y', strtotime($user['created_at'])) ?>
                </div>
              </div>
            </div>

            <!-- Formulaire -->
            <form method="POST" action="./model/user-crud.php" enctype="multipart/form-data">
              <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">

              <div class="form-card__body">

                <!-- Avatar section -->
                <div class="avatar-section">
                  <?php if (!empty($user['photo_user'])): ?>
                    <img id="avatarPreview" class="avatar-preview"
                         src="../../../public/img/users/<?= htmlspecialchars($user['photo_user']) ?>"
                         alt="Photo de <?= htmlspecialchars($user['name_user']) ?>">
                  <?php else: ?>
                    <div class="avatar-placeholder" id="avatarInitials"><?= $initials ?></div>
                    <img id="avatarPreview" class="avatar-preview"
                         src="" alt="" style="display:none;">
                  <?php endif; ?>

                  <div class="avatar-info">
                    <div class="avatar-name">
                      <?= htmlspecialchars($user['name_user']) ?>
                      <?= htmlspecialchars($user['lastname_user']) ?>
                    </div>
                    <p><?= htmlspecialchars($user['email_user']) ?></p>
                    <label class="upload-btn" for="photo_user">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                      </svg>
                      Changer la photo
                    </label>
                    <input type="file" id="photo_user" name="photo_user"
                           accept="image/*" style="display:none;">
                  </div>
                </div>

                <!-- Champs -->
                <div class="form-grid">

                  <!-- Section : Identité -->
                  <div class="form-section-label">Identité</div>

                  <div class="form-group">
                    <label class="form-label" for="name_user">
                      Prénom <span>*</span>
                    </label>
                    <input type="text" id="name_user" name="name_user" class="form-input"
                           value="<?= htmlspecialchars($user['name_user']) ?>"
                           placeholder="Jean" required>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="lastname_user">
                      Nom <span>*</span>
                    </label>
                    <input type="text" id="lastname_user" name="lastname_user" class="form-input"
                           value="<?= htmlspecialchars($user['lastname_user']) ?>"
                           placeholder="Dupont" required>
                  </div>

                  <div class="form-group full">
                    <label class="form-label" for="email_user">
                      Adresse email <span>*</span>
                    </label>
                    <input type="email" id="email_user" name="email_user" class="form-input"
                           value="<?= htmlspecialchars($user['email_user']) ?>"
                           placeholder="exemple@email.com" required>
                  </div>

                  <!-- Section : Accès -->
                  <div class="form-section-label">Accès &amp; permissions</div>

                  <div class="form-group">
                    <label class="form-label" for="role_user">Rôle</label>
                    <select id="role_user" name="role_user" class="form-select">
                      <option value="admin"  <?= $user['role_user'] === 'admin'  ? 'selected' : '' ?>>Admin</option>
                      <option value="editor" <?= $user['role_user'] === 'editor' ? 'selected' : '' ?>>Éditeur</option>
                      <option value="viewer" <?= $user['role_user'] === 'viewer' ? 'selected' : '' ?>>Lecteur</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="statut_user">Statut</label>
                    <select id="statut_user" name="statut_user" class="form-select statut-select">
                      <option value="actif"    <?= $user['statut_user'] === 'actif'    ? 'selected' : '' ?>>Actif</option>
                      <option value="inactif"  <?= $user['statut_user'] === 'inactif'  ? 'selected' : '' ?>>Inactif</option>
                      <option value="suspendu" <?= $user['statut_user'] === 'suspendu' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                  </div>

                  <!-- Section : Sécurité -->
                  <div class="form-section-label">Sécurité</div>

                  <div class="form-group full">
                    <label class="form-label" for="password_user">
                      Nouveau mot de passe
                    </label>
                    <div class="pwd-wrap">
                      <input type="password" id="password_user" name="password_user"
                             class="form-input" placeholder="Laisser vide pour ne pas modifier"
                             minlength="6">
                      <button type="button" class="pwd-toggle" id="togglePwd" title="Afficher/Masquer">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                          <circle cx="12" cy="12" r="3"/>
                        </svg>
                      </button>
                    </div>
                    <span class="form-hint">
                      Laissez ce champ vide si vous ne souhaitez pas modifier le mot de passe.
                    </span>
                  </div>

                </div><!-- .form-grid -->
              </div><!-- .form-card__body -->

              <!-- Footer avec boutons -->
              <div class="form-card__footer">
                <a href="users.php" class="btn-cancel">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                  </svg>
                  Annuler
                </a>

                <div style="display:flex; gap:10px; align-items:center;">
                  <?php if (!empty($user['derniere_connexion'])): ?>
                    <span style="font-size:12px; color:#b9b9b9;">
                      Dernière connexion :
                      <?= date('d M Y à H:i', strtotime($user['derniere_connexion'])) ?>
                    </span>
                  <?php endif; ?>
                  <button type="submit" name="update_user" class="primary-default-btn"
                          style="font-size:14px; padding:9px 20px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
                      <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/>
                      <polyline points="17 21 17 13 7 13 7 21"/>
                      <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Enregistrer les modifications
                  </button>
                </div>
              </div>
            </form>
          </div><!-- .form-card -->

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
    // Preview avatar à l'upload
    const photoInput   = document.getElementById('photo_user');
    const avatarImg    = document.getElementById('avatarPreview');
    const avatarInit   = document.getElementById('avatarInitials');
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

    // Toggle password visibility
    const toggleBtn = document.getElementById('togglePwd');
    const pwdInput  = document.getElementById('password_user');
    const eyeIcon   = document.getElementById('eyeIcon');
    const eyeOff    = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    const eyeOn     = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    let visible = false;
    toggleBtn?.addEventListener('click', () => {
      visible = !visible;
      pwdInput.type = visible ? 'text' : 'password';
      eyeIcon.innerHTML = visible ? eyeOff : eyeOn;
    });
  </script>
</body>
</html>
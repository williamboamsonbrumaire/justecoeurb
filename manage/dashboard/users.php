<?php
require_once "./model/user-crud.php";

// ── Pagination ────────────────────────────────────────────────────────────────
$per_page = 10;
$page     = max(1, (int) ($_GET['page'] ?? 1));
$offset   = ($page - 1) * $per_page;

function countRows(PDO $db, string $table): int {
    return (int) $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}

$total_users = countRows($conDB, 'users');
$users       = getAllUsers($conDB, $per_page, $offset);
$total_pages = (int) ceil($total_users / $per_page);

function paginationUrl(string $param, int $page): string {
    $params = $_GET;
    $params[$param] = $page;
    return '?' . http_build_query($params);
}

// Badge success
$success = $_GET['success'] ?? '';
$success_msg = match($success) {
    'added'   => 'Utilisateur ajouté avec succès.',
    'updated' => 'Utilisateur modifié avec succès.',
    'deleted' => 'Utilisateur supprimé avec succès.',
    default   => ''
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Utilisateurs</title>
  <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
  <style>
    /* ── Breadcrumb ── */
    .breadcrumb {
      display: flex; align-items: center; gap: 6px;
      font-size: 13px; color: #b9b9b9; margin-bottom: 20px;
    }
    .breadcrumb a  { color: #2f49d1; text-decoration: none; }
    .breadcrumb svg { width: 14px; height: 14px; }

    /* ── Section bar ── */
    .section-bar {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
    }
    .section-bar__left { display: flex; align-items: center; gap: 10px; }
    .section-bar__icon {
      width: 36px; height: 36px; border-radius: 8px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
      background: rgba(47,73,209,0.1); color: #2f49d1;
    }
    .section-bar__title { font-weight: 700; font-size: 17px; color: #171717; }
    .section-bar__count { font-size: 12px; color: #b9b9b9; font-weight: 500; }

    /* ── Toast ── */
    .toast {
      display: flex; align-items: center; gap: 10px;
      background: #edfaf3; border: 1px solid #b7eacf;
      color: #1a7a45; border-radius: 10px;
      padding: 12px 16px; font-size: 13px; font-weight: 600;
      margin-bottom: 20px; animation: slideIn .3s ease;
    }
    @keyframes slideIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
    .toast svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* ── Search bar ── */
    .search-bar {
      display: flex; align-items: center; gap: 10px;
      background: #fff; border: 1px solid #eeeeee;
      border-radius: 10px; padding: 0 14px;
      height: 40px; width: 260px;
    }
    .search-bar svg { color: #b9b9b9; width: 16px; height: 16px; flex-shrink: 0; }
    .search-bar input {
      border: none; background: transparent; outline: none;
      font-size: 13px; color: #171717; width: 100%;
    }
    .search-bar input::placeholder { color: #b9b9b9; }

    /* ── Role / statut badges ── */
    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 3px 10px; border-radius: 20px;
      font-size: 11px; font-weight: 700; letter-spacing: .3px;
    }
    .badge-admin    { background: rgba(47,73,209,.1);  color: #2f49d1; }
    .badge-editor   { background: rgba(255,171,0,.1);  color: #b37d00; }
    .badge-viewer   { background: rgba(160,163,189,.15); color: #767676; }
    .badge-actif    { background: rgba(16,185,129,.1); color: #0d7a55; }
    .badge-inactif  { background: rgba(242,100,100,.1); color: #c94040; }
    .badge-suspendu { background: rgba(255,171,0,.1);  color: #b37d00; }
    .badge-dot {
      width: 6px; height: 6px; border-radius: 50%; background: currentColor;
    }

    /* ── Users table ── */
    .users-table-wrap {
      background: #fff; border-radius: 14px;
      box-shadow: 0 2px 12px rgba(160,163,189,.10);
      overflow: hidden;
    }
    .users-table {
      width: 100%; border-collapse: collapse;
    }
    .users-table thead tr {
      background: #f7f8fc;
      border-bottom: 1px solid #eeeeee;
    }
    .users-table thead th {
      padding: 12px 16px; text-align: left;
      font-size: 11px; font-weight: 700;
      color: #b9b9b9; text-transform: uppercase; letter-spacing: .6px;
      white-space: nowrap;
    }
    .users-table tbody tr {
      border-bottom: 1px solid #f3f3f3;
      transition: background .15s;
    }
    .users-table tbody tr:last-child { border-bottom: none; }
    .users-table tbody tr:hover { background: #f9faff; }
    .users-table td {
      padding: 13px 16px; font-size: 13px; color: #444; vertical-align: middle;
    }

    /* ── Avatar user ── */
    .user-avatar-wrap { display: flex; align-items: center; gap: 11px; }
    .user-avatar {
      width: 40px; height: 40px; border-radius: 50%; object-fit: cover;
      flex-shrink: 0; background: #eff0f6;
    }
    .user-avatar-placeholder {
      width: 40px; height: 40px; border-radius: 50%;
      background: linear-gradient(135deg, #2f49d1 0%, #5877f2 100%);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 700; font-size: 14px; flex-shrink: 0;
      letter-spacing: .5px;
    }
    .user-name { font-weight: 600; font-size: 14px; color: #171717; line-height: 1.3; }
    .user-id   { font-size: 11px; color: #b9b9b9; margin-top: 1px; }

    /* ── Email cell ── */
    .user-email {
      display: flex; align-items: center; gap: 6px;
      font-size: 13px; color: #767676;
    }
    .user-email svg { width: 13px; height: 13px; color: #d6d7e3; flex-shrink: 0; }

    /* ── Last connexion ── */
    .last-conn { font-size: 12px; color: #b9b9b9; }

    /* ── Action buttons ── */
    .action-btn {
      width: 32px; height: 32px; border-radius: 8px; border: none;
      cursor: pointer; display: inline-flex; align-items: center;
      justify-content: center; transition: .2s all; padding: 0;
      text-decoration: none;
    }
    .action-btn.edit   { background: rgba(47,73,209,.08);  color: #2f49d1; }
    .action-btn.delete { background: rgba(242,100,100,.08); color: #f26464; }
    .action-btn:hover  { filter: brightness(.9); transform: scale(1.05); }
    .action-btn svg    { width: 14px; height: 14px; }
    .actions-cell      { display: flex; align-items: center; gap: 6px; }

    /* ── Empty state ── */
    .empty-row td {
      text-align: center; padding: 60px 20px;
      color: #b9b9b9; font-size: 14px; font-weight: 500;
    }
    .empty-icon { opacity: .3; margin-bottom: 10px; }

    /* ── Pagination ── */
    .pagination-wrapper {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 10px;
      padding: 14px 16px; border-top: 1px solid #f3f3f3;
    }
    .pagination-info { font-size: 13px; color: #b9b9b9; font-weight: 500; }
    .pagination { display: flex; align-items: center; gap: 4px; }
    .pagination a, .pagination span {
      display: flex; align-items: center; justify-content: center;
      min-width: 34px; height: 34px; padding: 0 8px;
      border-radius: 8px; font-size: 13px; font-weight: 600;
      text-decoration: none; transition: .2s all; color: #767676;
      background: #fff; border: 1px solid #eeeeee;
    }
    .pagination a:hover { background: #eff0f6; color: #2f49d1; border-color: #d6d7e3; }
    .pagination a.active { background: #2f49d1; color: #fff; border-color: #2f49d1; }
    .pagination span.dots { border: none; background: transparent; color: #b9b9b9; }
    .pagination a.disabled { opacity: .35; pointer-events: none; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      .users-table thead th:nth-child(4),
      .users-table tbody td:nth-child(4),
      .users-table thead th:nth-child(5),
      .users-table tbody td:nth-child(5) { display: none; }
      .search-bar { width: 100%; }
    }
    @media (max-width: 480px) {
      .users-table thead th:nth-child(3),
      .users-table tbody td:nth-child(3) { display: none; }
    }

    /* ── Dark mode ── */
    .darkmode .users-table-wrap  { background: #222235; box-shadow: none; }
    .darkmode .users-table thead tr { background: #1a1a2e; border-color: #37374B; }
    .darkmode .users-table tbody tr { border-color: #2e2e45; }
    .darkmode .users-table tbody tr:hover { background: #2a2a40; }
    .darkmode .users-table td   { color: #D6D7E3; }
    .darkmode .user-name        { color: #EFF0F6; }
    .darkmode .search-bar       { background: #222235; border-color: #37374B; }
    .darkmode .search-bar input { color: #EFF0F6; }
    .darkmode .pagination-wrapper { border-color: #37374B; }
    .darkmode .pagination a, .darkmode .pagination span {
      background: #222235; border-color: #37374B; color: #D6D7E3;
    }
    .darkmode .pagination a.active { background: #2f49d1; border-color: #2f49d1; color: #fff; }
    .darkmode .section-bar__title { color: #EFF0F6; }
    .darkmode .toast { background: #1a2e24; border-color: #2d6645; color: #5ac98a; }
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
            <span>Utilisateurs</span>
          </nav>

          <!-- Toast success -->
          <?php if ($success_msg): ?>
            <div class="toast" id="toast-success">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
              <?= htmlspecialchars($success_msg) ?>
            </div>
            <script>setTimeout(() => document.getElementById('toast-success')?.remove(), 4000);</script>
          <?php endif; ?>

          <!-- Title + bouton ajouter -->
          <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
            <h2 class="main-title" style="margin-bottom:0;">Utilisateurs</h2>
            <a href="add-user.php" class="primary-default-btn" style="font-size:14px; padding:9px 16px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                   fill="none" stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              Ajouter un utilisateur
            </a>
          </div>

          <!-- Section bar -->
          <div class="section-bar">
            <div class="section-bar__left">
              <div class="section-bar__icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
              </div>
              <div>
                <div class="section-bar__title">Liste des utilisateurs</div>
                <div class="section-bar__count"><?= $total_users ?> utilisateur<?= $total_users > 1 ? 's' : '' ?></div>
              </div>
            </div>
            <!-- Recherche -->
            <div class="search-bar">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="text" id="userSearch" placeholder="Rechercher un utilisateur…">
            </div>
          </div>

          <!-- Table -->
          <div class="users-table-wrap">
            <table class="users-table" id="usersTable">
              <thead>
                <tr>
                  <th>Utilisateur</th>
                  <th>Email</th>
                  <th>Rôle</th>
                  <th>Statut</th>
                  <th>Dernière connexion</th>
                  <th style="text-align:right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($users)): ?>
                  <tr class="empty-row">
                    <td colspan="6">
                      <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="1.5">
                          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                          <circle cx="9" cy="7" r="4"/>
                          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                      </div>
                      <div>Aucun utilisateur trouvé.</div>
                      <a href="add_user.php" class="primary-default-btn"
                         style="font-size:13px;padding:8px 16px;display:inline-flex;margin-top:12px;">
                        Ajouter le premier
                      </a>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($users as $u):
                    $initials = strtoupper(mb_substr($u['name_user'], 0, 1) . mb_substr($u['lastname_user'], 0, 1));
                    $role_class = match($u['role_user']) {
                        'admin'  => 'badge-admin',
                        'editor' => 'badge-editor',
                        default  => 'badge-viewer'
                    };
                    $statut_class = match($u['statut_user']) {
                        'actif'    => 'badge-actif',
                        'inactif'  => 'badge-inactif',
                        default    => 'badge-suspendu'
                    };
                  ?>
                    <tr>
                      <!-- Avatar + nom -->
                      <td>
                        <div class="user-avatar-wrap">
                          <?php if (!empty($u['photo_user'])): ?>
                            <img class="user-avatar"
                                 src="../../../public/img/users/<?= htmlspecialchars($u['photo_user']) ?>"
                                 alt="<?= htmlspecialchars($u['name_user']) ?>">
                          <?php else: ?>
                            <div class="user-avatar-placeholder"><?= $initials ?></div>
                          <?php endif; ?>
                          <div>
                            <div class="user-name">
                              <?= htmlspecialchars($u['name_user']) ?>
                              <?= htmlspecialchars($u['lastname_user']) ?>
                            </div>
                            <div class="user-id">#<?= $u['id_user'] ?></div>
                          </div>
                        </div>
                      </td>

                      <!-- Email -->
                      <td>
                        <div class="user-email">
                          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                               stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                          </svg>
                          <?= htmlspecialchars($u['email_user']) ?>
                        </div>
                      </td>

                      <!-- Rôle -->
                      <td>
                        <span class="badge <?= $role_class ?>">
                          <span class="badge-dot"></span>
                          <?= htmlspecialchars(ucfirst($u['role_user'])) ?>
                        </span>
                      </td>

                      <!-- Statut -->
                      <td>
                        <span class="badge <?= $statut_class ?>">
                          <span class="badge-dot"></span>
                          <?= htmlspecialchars(ucfirst($u['statut_user'])) ?>
                        </span>
                      </td>

                      <!-- Dernière connexion -->
                      <td class="last-conn">
                        <?= !empty($u['derniere_connexion'])
                            ? date('d M Y à H:i', strtotime($u['derniere_connexion']))
                            : '<span style="color:#d6d7e3;">Jamais</span>' ?>
                      </td>

                      <!-- Actions -->
                      <td>
                        <div class="actions-cell" style="justify-content:flex-end;">
                          <a href="edit_user.php?id=<?= $u['id_user'] ?>"
                             class="action-btn edit" title="Modifier">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                          </a>
                          <form method="POST" action="./model/user-crud.php"
                                onsubmit="return confirm('Supprimer cet utilisateur ?');" style="margin:0;">
                            <input type="hidden" name="id_user" value="<?= $u['id_user'] ?>">
                            <button type="submit" name="delete_user"
                                    class="action-btn delete" title="Supprimer">
                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                <path d="M10 11v6"/><path d="M14 11v6"/>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                              </svg>
                            </button>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
              <div class="pagination-wrapper">
                <span class="pagination-info">
                  <?= min($offset + 1, $total_users) ?>–<?= min($offset + $per_page, $total_users) ?>
                  sur <?= $total_users ?> utilisateurs
                </span>
                <nav class="pagination">
                  <a href="<?= paginationUrl('page', $page - 1) ?>"
                     class="<?= $page <= 1 ? 'disabled' : '' ?>" title="Précédent">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5">
                      <polyline points="15 18 9 12 15 6"/>
                    </svg>
                  </a>

                  <?php for ($i = 1; $i <= $total_pages; $i++):
                    if ($i === 1 || $i === $total_pages || abs($i - $page) <= 1): ?>
                      <a href="<?= paginationUrl('page', $i) ?>"
                         class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php elseif (abs($i - $page) === 2): ?>
                      <span class="dots">…</span>
                    <?php endif;
                  endfor; ?>

                  <a href="<?= paginationUrl('page', $page + 1) ?>"
                     class="<?= $page >= $total_pages ? 'disabled' : '' ?>" title="Suivant">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5">
                      <polyline points="9 18 15 12 9 6"/>
                    </svg>
                  </a>
                </nav>
              </div>
            <?php endif; ?>
          </div><!-- .users-table-wrap -->

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
    // Recherche en temps réel
    const searchInput = document.getElementById('userSearch');
    const tableRows   = document.querySelectorAll('#usersTable tbody tr:not(.empty-row)');
    searchInput?.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase();
      tableRows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
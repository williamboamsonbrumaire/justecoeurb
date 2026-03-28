<?php
require_once __DIR__ . '../../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Connexion();
global $conDB;

if (!isset($_SESSION['id_user'])) {
    // Redirection vers la page de login
    header('Location: ../login.php');
    exit(); // Très important : on arrête le script ici
}
?> 

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur BeauBrun | Dashboard</title>
 <link rel="shortcut icon" href="../assets/img/svg/logo.svg" type="image/x-icon">
  <link rel="stylesheet" href="../assets/css/style.min.css">
</head>

<body>
  <div class="layer"></div>
    <!-- ! Body -->
    <a class="skip-link sr-only" href="#skip-target">Skip to content</a>
    <div class="page-flex">
      <!-- ! Sidebar -->
    <?php 
    include "./inside/aside.php"
    ?>
      <div class="main-wrapper">
        <!-- ! Main nav -->
      <?php 
    include "./inside/nav.php"
    ?>
    <!-- ! Main -->
    <main class="main users chart-page" id="skip-target">
      <div class="container">
        <h2 class="main-title">Bienvenue</h2>
      </div>
    </main>
    <!-- ! Footer -->
    <?php 
    include "./inside/footer.php"
    ?>
  </div>
</div>
<!-- Chart library -->
<script src="../assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="../assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="../assets/js/script.js"></script>
</body>

</html>
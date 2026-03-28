<?php
include 'model/auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Juste Coeur Dashboard | Sign In</title>
  <!-- Favicon -->

  <!-- Custom styles -->
  <link rel="stylesheet" href="./assets/css/style.min.css">
</head>

<body>
  <div class="layer"></div>
<main class="page-center">
  <article class="sign-up">
    <!-- <h1 class="sign-up__title">Welcome back!</h1> -->
  
    <form class="sign-up-form form" action="" method="POST">
      <label class="form-label-wrapper">
        <p class="form-label">Email</p>
        <input class="form-input" name="email_login" type="email" placeholder="Enter your email" required>
      </label>
      <label class="form-label-wrapper">
        <p class="form-label">Password</p>
        <input class="form-input" name="pwd_login" type="password" placeholder="Enter your password" required>
      </label>
      <a class="link-info forget-link" href="##">Forgot your password?</a>
      <label class="form-checkbox-wrapper">
        <input class="form-checkbox" type="checkbox" required>
        <span class="form-checkbox-label">Remember me next time</span>
      </label>
      <button  type="submit" name="connexion" class="form-btn primary-default-btn transparent-btn">Sign in</button>
    </form>
  </article>
</main>
<!-- Chart library -->
<script src="./assets/plugins/chart.min.js"></script>
<!-- Icons library -->
<script src="./assets/plugins/feather.min.js"></script>
<!-- Custom scripts -->
<script src="./assets/js/script.js"></script>
</body>

</html>
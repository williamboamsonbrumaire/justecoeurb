<?php
// On remonte au dossier parent de "includes" pour trouver la racine, puis on pointe vers config.php
// Cela fonctionne que tu sois dans /index.php, /pages/about.php ou /blog/article.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/jcb/includes/config.php';
?>

<!-- ================== FOOTER ================== -->
<footer data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="footer">
  <p style="color:#000;" class="text-dark">&copy; <script>document.write(new Date().getFullYear());</script> Juste-Cœur Beaubrun. Tous droits réservés.</p>

  <div class="footer-links">
    <a style="color:#000;" href="<?php echo base_url('pages/mentionlegal'); ?>">Mentions légales</a>
    <a style="color:#000;" href="<?php echo base_url('pages/politiquedeconfidentialite'); ?>">Politique de confidentialité</a>
  </div>
</footer>

<script src="<?php echo base_url('public/js/jquery-1.12.1.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/popper.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/jquery.magnific-popup.js'); ?>"></script>
<script src="<?php echo base_url('public/js/masonry.pkgd.js'); ?>"></script>
<script src="<?php echo base_url('public/js/owl.carousel.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/jquery.nice-select.min.js'); ?>"></script>
<script src="<?php echo base_url('public/js/custom.js'); ?>"></script>
<script src="<?php echo base_url('public/js/testimonial.js'); ?>"></script>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Google Translate (invisible) -->
<div id="google_translate_element" style="display:none;"></div>
<script src="<?php echo base_url('public/js/global.js'); ?>"></script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
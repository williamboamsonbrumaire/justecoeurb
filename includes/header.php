<?php
// On remonte au dossier parent de "includes" pour trouver la racine, puis on pointe vers config.php
// Cela fonctionne que tu sois dans /index.php, /pages/about.php ou /blog/article.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/jcb/includes/config.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="icon" href="<?php echo base_url('public/img/banner_bg_2.png'); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo base_url('public/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/animate.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/owl.carousel.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/themify-icons.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/flaticon.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/magnific-popup.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/slick.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/gijgo.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/nice-select.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/all.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('public/css/style.css'); ?>">
</head>

<section class="bg-white">
    <header class="main_menu home_menu">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="logo" href="<?php echo base_url(); ?>">
                            <h3 class="m-0">Juste-Cœur <span>Beaubrun</span></h3>
                            <small>LEADERSHIP · INNOVATION · JEUNESSE</small>
                        </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="menu_icon"><i class="ti-menu"></i></span>
                        </button>

                        <div class="collapse navbar-collapse main-menu-item" id="navbarSupportedContent">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo base_url('about'); ?>">A propos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo base_url('projet'); ?>">Engagements</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo base_url('publication'); ?>">Publications</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo base_url('blog'); ?>">Blog</a>
                                </li>

                                <li class="nav-item translate-dropdown dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Langues
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('fr')"><img src="https://flagcdn.com/24x18/fr.png" alt=""> Français</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('en')"><img src="https://flagcdn.com/24x18/gb.png" alt=""> English</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('es')"><img src="https://flagcdn.com/24x18/es.png" alt=""> Español</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('ht')"><img src="https://flagcdn.com/24x18/ht.png" alt=""> Kreyòl Ayisyen</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('pt')"><img src="https://flagcdn.com/24x18/pt.png" alt=""> Português</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="social_icon">
                            <a href="http://facebook.com/justecoeur.beaubrun" target="_blank"><i class="fab fa-facebook-square"></i></a>
                            <a href="http://instagram.com/justecoeurb" target="_blank"><i class="fab fa-instagram"></i></a>
                            <a href="http://linkedin.com/in/justecoeurbeaubrun" target="_blank"><i class="fab fa-linkedin"></i></a>
                            <a href="http://x.com/justecoeurb" target="_blank"><i class="fab fa-twitter"></i></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</section>



<!-- <head>
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="icon" href="public/img/banner_bg_2.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/animate.css">
    <link rel="stylesheet" href="public/css/owl.carousel.min.css">
    <link rel="stylesheet" href="public/css/themify-icons.css">
    <link rel="stylesheet" href="public/css/flaticon.css">
    <link rel="stylesheet" href="public/css/magnific-popup.css">
    <link rel="stylesheet" href="public/css/slick.css">
    <link rel="stylesheet" href="public/css/gijgo.min.css">
    <link rel="stylesheet" href="public/css/nice-select.css">
    <link rel="stylesheet" href="public/css/all.css">
    <link rel="stylesheet" href="public/css/style.css">
</head>

<section class="bg-white ">
    <header class="main_menu home_menu">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <a class="logo" href="/jcb/">
                            <h3 class="m-0">Juste-Cœur <span>Beaubrun</span></h3>
                            <small>LEADERSHIP · INNOVATION · JEUNESSE</small>
                         </a>
                        <button class="navbar-toggler" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                            <span class="menu_icon"><i class="ti-menu"></i></span>
                        </button>

                        <div class="collapse navbar-collapse main-menu-item" id="navbarSupportedContent">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" href="pages/about.php">A propos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="pages/projet.php">Engagements</a>
                                </li>
                                
                                <li class="nav-item">
                                    <a class="nav-link" href="pages/publication.php">Publications</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="blog/index.php">Blog</a>
                                </li>

                                <li class="nav-item translate-dropdown dropdown">
                                    <a class="nav-link  dropdown-toggle" href="pages/blog.php" id="navbarDropdown"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Langues
                                    </a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('fr')"><img src="https://flagcdn.com/24x18/fr.png" alt=""> Français</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('en')"><img src="https://flagcdn.com/24x18/gb.png" alt=""> English</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('es')"><img src="https://flagcdn.com/24x18/es.png" alt=""> Español</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('ht')"><img src="https://flagcdn.com/24x18/ht.png" alt=""> Kreyòl Ayisyen</a>
                                        <a class="dropdown-item" href="#" onclick="translateLanguage('pt')"><img src="https://flagcdn.com/24x18/pt.png" alt=""> Português</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="social_icon">
                            <a href="http://facebook.com/justecoeur.beaubrun"><i class="fab fa-facebook-square"></i></a>
                            <a href="http://instagram.com/justecoeurb"><i class="fab fa-instagram"></i></a>
                            <a href="http://linkedin.com/in/justecoeurbeaubrun"><i class="fab fa-linkedin"></i></a>
                            <a href="http://x.com/justecoeurb"><i class="fab fa-twitter"></i></a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

  </section>
    <!-- Header part end-->

    <!-- Header part end-->
    <!-- SECTION BLEUE -->

    -->

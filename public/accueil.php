<?php
require_once "manage/dashboard/model/post-crud.php";
require_once  'manage/dashboard/model/reviews-crud.php';

$articles        = getAllArticles($conDB);
$reviews = getAllReviews($conDB);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta property="og:title" content="Juste-Coeur BEAUBRUN: Économiste et Innovateur Social" />
    <meta property="og:description" content="Découvrez les engagements et le parcours de Juste-Cœur Beaubrun" />
    <meta property="og:url" content="https://justecoeurb.ht/" />
    <meta property="og:image" content="https://justecoeurb.ht/img/jc_speaking.jpg" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://justecoeurb.ht/img/jc_speaking.jpg">

    
    <title>Juste Coeur Beaubrun</title>

</head>
<body>
    <!--::header part start::-->

<?php
require_once 'includes/header.php';
?>

 <section class="hero-blue position-relative py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <span class="badge bg-light text-primary mb-3">
          Economiste · Innovateur Social
        </span>

        <h1 data-aos="fade-up"  class="display-5 fw-bold text-white">
          Engagé <span class="text-primary">pour <br>les jeunes et les communautés</span>
        </h1>

        <p data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="text-white mt-3">
          Jeune haïtien, Juste-Cœur accompagne les jeunes, les communautés 
          et les organisations dans la création de solutions durables, à la croisée de 
          l’innovation, de l’éducation et du développement local.
        </p>

        <div class=" gap-3 mt-4">
          <a href="pages/contact.php" class="btn btn-primary mt-3 px-4 py-2">Proposer une collaboration</a>
          <a href="pages/about.php" class="btn btn-outline mt-3 px-4 py-2">Découvrir son parcours</a>
        </div>

        <ul class="mt-4 text-white">
          <li class="mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg> Engagement citoyen</li>
          <li class="mb-2"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg> Innovation sociale</li>
          <li><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#007bff" class="bi bi-check-circle-fill me-2" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                        </svg> Bâtisseur de communautés</li>
        </ul>
      </div>
    </div>
  </div>
  <img src="public/img/banner_bg_2.png" data-aos="zoom-in" data-aos-duration="1500" class="hero-abs-img" alt="Portrait">
</section>
<!-- SECTION BLANCHE -->
<section class="hero-white py-5">
  <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="container">
    <div class="col-lg-7">
    <h2 class="fw-bold">
      <span class="text-primary">Un parcours</span> qui relie le local et le global
    </h2>

    <p class="text-dark">
      Du Nord-Ouest rural d'Haïti aux scènes internationales, Juste-Cœur construit 
      des ponts entre jeunesse, innovation et développement.
    </p>

    <a href="pages/about.php" class="mt-3 text-primary">Lire la biographie complète →</a>
    </div>
    
  </div>
</section>

<section data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="timeline-section py-5 bg-light">
    <div class="container">
        <div class="row position-relative">
            <div class="timeline-connector d-none d-lg-block"></div>
            <div class="timeline-dot"></div>
            <div class="col-lg timeline-box">
                
                <h5 class="fw-bold mt-3">Bivouac, Môle Saint-Nicolas</h5>
                <p class="text-muted">
                    Origines, engagement communautaire et contact direct avec les réalités du territoire.
                </p>
            </div>
            <div class="timeline-dot"></div>
            <div class="col-lg timeline-box">
                
                <h5 class="fw-bold mt-3">Université Notre Dame d'Haïti</h5>
                <p class="text-muted">
                    Licence en économie, compréhension des dynamiques macro et micro-économiques.
                </p>
            </div>
            <div class="timeline-dot"></div>
            <div class="col-lg timeline-box">
                
                <h5 class="fw-bold mt-3">HELP & OSUN Bard College</h5>
                <p class="text-muted">
                    Parcours en leadership, citoyenneté, IT et entreprise sociale.
                </p>
            </div>
            <div class="timeline-dot"></div>
            <div class="col-lg timeline-box">
                <h5 class="fw-bold mt-3">PLES</h5>
                <p class="text-muted">
                    Co-fondateur du Programme de Leadership et d’Entrepreneuriat Scolaire.
                </p>
            </div>
            <div class="timeline-dot"></div>
              <div class="col-lg timeline-box">
                  <h5 class="fw-bold mt-3">Banj Labs</h5>
                  <p class="text-muted">
                      Direction de l’incubation, de la recherche et de programmes d’innovation.
                  </p>
              </div>
        </div>
    </div>
</section>

<!-- =========================
     SECTION MISSION!-->
<section class="mission-section py-5">
    <div class="container">
        <div class="row align-items-center gy-5">

            <!-- Images à gauche -->
            <div class="col-lg-6 d-flex gap-3 justify-content-center">
                  <img src="./public/img/jc_father.JPG" class="mission-img" alt="Photo Collage JusteCoeur et Son père">
                </div>
                
              
            <!-- Bloc Mission à droite -->
            <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="col-lg-6">
                <div class="mission-card p-4 p-lg-5">
                    <span class="text-uppercase small text-secondary">Mission</span>
                    <h3 class="fw-bold fs-5 text-white mt-3">
                        Créer des écosystèmes où les jeunes ne sont pas seulement invités à participer, 
                        mais outillés pour transformer leurs communautés.
                    </h3>
                    <p class="text-white-50 mt-3">
                        À travers ses actions, Juste-Cœur travaille à rendre l’innovation accessible, 
                        à faire circuler les opportunités et à créer des ponts entre communautés, 
                        institutions et talents. </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================
     SECTION PROJETS
========================== -->
<section class="projects my-5">
  <div class="container">
    <div class="row">
    <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="col-12 ">
      <div class="justify-content-center align-items-center text-center">
        <h2 class="display-5 fw-bold">Projets et programmes <br> auxquels Juste-Coeur Beaubrun a contribué</h2>
            <div class="container w-100 d-flex justify-content-center">
                <p class="lead col-lg-7 text-muted mb-4">
                Des programmes qui connectent les jeunes aux outils, mentors et opportunités
                dont ils ont besoin pour transformer leurs communautés.
            </p>
            </div>
            </div>
            
        <div class="d-flex justify-content-lg-end">
        <a href="pages/projet.php" class="mb-5  explore">Explorer tous les projets →</a>
        
                </div>
                </div>
                </div>

            <div class="row justify-content-center">

                <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                    <h3 class="card-title h5 text-primary">Creative Tech Lab</h3>
                    <p class="card-text">
                        Creative
        Tech Lab
        is an open collaboration tool that brings together diverse individuals interested in designing the future of the creative industries.
                    </p>
                    <a href="https://creativetlab.com/" class="btn-outline mt-4 px-4 py-2">Voir le projet →</a>
              
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
              <h3 class="card-title h5 text-primary">Programme d’Idéation Communautaire (PIC)</h3>
              <p class="card-text">
                Le projet PIC a pour objectif de renforcer les capacités des jeunes en Haïti en leur offrant des opportunités de développement dans des domaines tels que l'entrepreneuriat et le leadership, et en encourageant leur engagement citoyen.
              </p>
              <a href="https://pic.banj.ht/" class="btn-outline mt-4 px-4 py-2">Voir le projet →</a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
              <h3 class="card-title h5 text-primary">DevExpo</h3>
              <p class="card-text">
                DevExpo est une initiative annuelle organisée par Banj spécifiquement pour et autour des développeurs haïtiens. Il s'agit du plus grand rassemblement de développeurs locaux en Haïti, visant à mettre en lumière leur talent et à stimuler l’innovation dans le pays.
              </p>
              <a href="https://www.devexpo.ht/" class="btn-outline mt-4 px-4 py-2">Voir le projet →</a>
            </div>
          </div>
        </div>
      </div>
  </div>
  

</section>

<!-- =========================
     Contact SECTION
========================== -->
<section class="contact-section">
   <div class="container">
   </div><div class="contact-box">
            <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="contact-text">
                  <span class="label">Planifier un échange</span>
                  <h2>Un court message,<br>et on avance.</h2>
                  <p>
                    Un sujet à éclaircir, une ressource manquante,
                    une idée à tester. Un message suffit.
                  </p>
                  <a href="https://calendly.com/justecoeurb" class="contact-btn">Discuter avec Juste-Coeur →</a>
              </div>

              <div class="contact-image">
                  <img src="./public/img/jc_speaking.jpg" alt="Conférence">
                  <div class="bubble bubble-right"><img src="./public/img/juste_coeut_web_assets_17.png" alt=""></div>
              </div>
      </div>
  </div>
</section>
<!-- ================= Distinction ================= -->

<section class="service_part padding_bottom">
    <div data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 mt-5">
                <div class=" text-center">
                    <h2>Distinctions et concours</h2>
                    <p class="text-dark -">
                    </p>
                </div>
            </div>
        </div>
        
        <div class="scrolling-container">
            <div class="scrolling-accomplishments">
                
                <div class="single_service_part">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>2004 – 2019 </h2>
                        <p>Bourse d’excellence du Collège La Fraternité, attribuée dès la maternelle pour l’ensemble du parcours scolaire</p>
                    </div>
                </div>
                <div class="single_service_part active">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>2016 </h2>
                        <p>1er prix du concours de texte – Centre de Promotion et de Production de l’Art (CPPA)</p>
                    </div>
                </div>
                <div class="single_service_part">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>2017 </h2>
                        <p> 2e prix du concours de texte – CAPOMAR</p>
                    </div>
                </div>
                <div class="single_service_part active">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>2018 et 2019</h2>
                        <p> 1er prix du concours de slam – Splendeur Groupe Haïti (SGH), remporté deux années consécutives</p>
                    </div>
                </div>
                
                <div class="single_service_part">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>2019</h2>
                        <p>Obtention de la bourse du Haitian Education and Leadership Program (HELP)</p>
                    </div>
                </div>
                <div class="single_service_part active">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>2019 – 2023</h2>
                        <p>Sélectionné trois fois sur la liste d’honneur de HELP pour excellence académique</p>
                    </div>
                </div>
                <div class="single_service_part active">
                    <div class="single_service_text">
                        <img class="img" src="public/img/juste_coeut_web_assets_5.png" alt="">
                        <h2>Global Peace Summit (GPS)
    2026</h2>
                            <p>
    Sélectionné pour représenter Haïti au GPS à Paris (France)</p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
</section>
<!-- ================= Quote Section ================= -->
<section data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="quote-section">
  <h3>Citation du jour</h3>
  <p class="col-lg-4">
    « Les jeunes ne sont pas seulement le futur, ils sont déjà des acteurs du présent.
    Notre responsabilité, c’est de leur donner l’espace, les outils et la confiance pour agir. »
   <br><span>- Juste-Coeur Beaubrun</span> 
  </p>
</section>

<!-- ================= Actualité Section ================= -->


<section data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="mt-4 actualites-reflections">
  <div class="container">
    <div class="row">

      <div class="header col-sm-12 col-lg-4">
        <div class="text-content">
          <h1>Actualités et réflexions</h1>
          <p>Articles, annonces de programmes, interventions et histoires de terrain.</p>

          <div>
            <a href="pages/publication.php" class="view-all">Voir tous les articles →</a>
          </div>
        </div>

        <div class="navigation-controls">
          <button class="nav-button prev-button" aria-label="Article précédent">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>

          <button class="nav-button next-button" aria-label="Article suivant">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>
      </div>


      <div class="carousel-container col-sm-12 col-lg-8">
        <div class="cards-wrapper">

          <?php if (!empty($articles)): ?>
            <?php foreach ($articles as $a): ?>

              <div class="card">

                <?php if (!empty($a['photo'])): ?>
                  <div class="image-placeholder"
                       style="background-image: url('<?= htmlspecialchars($a['photo']) ?>');">
                  </div>
                <?php else: ?>
                  <div class="image-placeholder"
                       style="background-image: url('<?= htmlspecialchars($a['photo']) ?>');">
                  </div>
                <?php endif; ?>

                <div class="content">

                  <span class="tag tag-reflexion">
                    <?= htmlspecialchars($a['author']) ?>
                  </span>

                  <h3>
                    <?= htmlspecialchars($a['description']) ?>
                  </h3>

                

                  <?php if (!empty($a['link_article'])): ?>
                    <a class="btn text-white btn-secondary mt-3 px-4 py-2"
                       href="<?= htmlspecialchars($a['link_article']) ?>"
                       target="_blank">
                       Lire plus
                    </a>
                  <?php endif; ?>

                </div>
              </div>

            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>
</section>

<!-- Témoignages -->
<section data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" class="testimonials">
  <div class="testimonials-container container">
    <div class="testimonials-text">
      <h2>Références & témoignages</h2>
      <p class="subtitle">
        Ils ont collaboré avec Juste-Cœur sur des programmes d’impact,
        des projets communautaires et des initiatives d’innovation.
      </p>

      <ul id="testimonials-list">
        <?php if (empty($reviews)): ?>
          <li class="testimonials-item active">
            <blockquote class="testimonial-quote">
              "Aucun témoignage n'est disponible pour le moment."
            </blockquote>
          </li>
        <?php else: ?>
          <?php 
          $index = 0;
          foreach ($reviews as $rv): 
            // On ne filtre que les actifs pour le site public
            if ($rv['statut'] !== 'actif') continue;
            
            $isActiveClass = ($index === 0) ? 'active' : '';
            // Ajustement du chemin de l'image (attention au dossier public/)
            $imgSrc = !empty($rv['photo']) ? $rv['photo'] : 'public/img/default-avatar.jpg';
          ?>
            <li class="testimonials-item <?= $isActiveClass ?>" data-image="<?= htmlspecialchars($imgSrc) ?>">
              <blockquote class="testimonial-quote">
                “ <?= htmlspecialchars($rv['quote']) ?> ”
              </blockquote>
              <div class="author">
                <strong class="testimonial-name"><?= htmlspecialchars($rv['nom']) ?></strong>
                <span class="testimonial-role">
                    <?= htmlspecialchars($rv['role']) ?> 
                    <?= !empty($rv['organisation']) ? '- ' . htmlspecialchars($rv['organisation']) : '' ?>
                </span>
              </div>
            </li>
          <?php 
          $index++;
          endforeach; 
          ?>
        <?php endif; ?>
      </ul>

      <div class="controls">
        <button onclick="prevTestimonial()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <button onclick="nextTestimonial()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>

    <div class="testimonials-image">
      <?php 
        // Image par défaut pour le premier affichage
        $firstPhoto = 'public/img/default-avatar.jpg';
        if (!empty($reviews)) {
            foreach($reviews as $r) {
                if($r['statut'] === 'actif') {
                    $firstPhoto = $r['photo'];
                    break;
                }
            }
        }
      ?>
      <img id="testimonial-image" src="<?= htmlspecialchars($firstPhoto) ?>" alt="Témoignage">
    </div>
  </div>
</section>

<section data-aos="fade-up" data-aos-delay="200" data-aos-duration="800" id="contact" style="margin-bottom: 0;" class="contact-section">
    <div style="overflow: visible;" class="container">
        <div class="left">
            <h1>Construire un<br>projet <span>ensemble ?</span></h1>

            <p>
                Vous représentez une organisation, une école, un média ou un programme pour les jeunes ?
                Juste-Cœur collabore avec des partenaires locaux, nationaux et internationaux afin de créer de l’impact durable pour les jeunes Haïtiens et bien au-delà.
            </p>

            <p>
                Partenariats, interventions, mentorat, programmes jeunesse : parlons de vos besoins
                et de vos objectifs.
            </p>

            <div class="buttons">
                <a href="https://calendly.com/justecoeurb" class="btn btn-secondary mt-4 px-4 py-2">
                    <i class="fas fa-calendar-alt me-2"></i> Réservez une réunion
                </a>

                <a href="public/docs/cv-justecoeurbeaubrun.pdf" id="download-cv" class="btn btn-primary mt-4 px-4 py-2" 
                   download>
                    <i class="fas fa-download me-2"></i> Télécharger le CV
                </a>
                
            </div>
            <a href="public/docs/bio-justecoeurbeaubrun.pdf" id="download-cv" class="btn btn-primary mt-4 px-4 py-2" 
                   download>
                    <i class="fas fa-download me-2"></i> Télécharger la bio
                </a>
            <a href="public/docs/PortFolio_JCB_GPC_YPF_GBS en-US.pdf" id="download-cv" class="btn btn-primary mt-4 px-4 py-2" 
                   download>
                    <i class="fas fa-download me-2"></i> Télécharger mon portfolio
            </a>
        </div>

        <div class="right">

          <form action="contact_process.php" method="POST" class="contact-form">
    
    <div class="mb-3">
        <label for="inputNom" class="form-label">Nom & organisation</label>
        <input type="text" 
               class="form-control" 
               id="inputNom" 
               name="nom" 
               placeholder="Votre nom, structure..." required>
    </div>
    
    <div class="mb-3">
        <label for="inputEmail" class="form-label">E-mail</label>
        <input type="email" 
               class="form-control" 
               id="inputEmail" 
               name="email" 
               placeholder="vous@organisation.org" required>
    </div>
    
    <div class="mb-3">
        <label for="selectDemande" class="form-label">Type de demande</label>
        <select class="form-select" id="selectDemande" name="type_demande" required>
            <option selected disabled value="">Sélectionnez un type</option>
            <option>Partenariat / programme</option>
            <option>Intervention</option>
            <option>Mentorat</option>
            <option>Autre demande</option>
        </select>
    </div>
    
    <div class="mb-3">
        <label for="textareaMessage" class="form-label">Message</label>
        <textarea class="form-control" 
                  id="textareaMessage" 
                  name="message" 
                  rows="5" 
                  placeholder="Parlez-nous de votre projet, du public visé, des dates envisagées..." required></textarea>
    </div>
    
    <button type="submit" class="btn btn-secondary mt-4 px-4 py-2">
        Envoyer la demande
    </button>
    </form>
            </div>
        </div>
</section>

<!-- Footer -->
<?php
require_once 'includes/footer.php';
?>

<!-- Footer -->
  
  <script>
    AOS.init({
      // Options globales qui peuvent être remplacées par des attributs de données (data-aos-...)
      duration: 1200, // Valeur par défaut pour la durée de l'animation (en ms)
      once: true      // Si l'animation ne doit se jouer qu'une seule fois lors du défilement
    });
  </script>
 
<script>
    const container = document.getElementById('cards');
    document.getElementById('nextBtn').onclick = () => {
    container.scrollBy({ left: 350, behavior: "smooth" });
    };
    document.getElementById('prevBtn').onclick = () => {
    container.scrollBy({ left: -350, behavior: "smooth" });
    };
</script>

    </script>

 <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('.cards-wrapper');
            const prevButton = document.querySelector('.prev-button');
            const nextButton = document.querySelector('.next-button');

            if (wrapper && prevButton && nextButton) {
                // Fonction pour défiler par largeur de carte
                const scrollAmount = 320; // 300px (min-width carte) + 20px (gap)

                nextButton.addEventListener('click', () => {
                    wrapper.scrollLeft += scrollAmount;
                });

                prevButton.addEventListener('click', () => {
                    wrapper.scrollLeft -= scrollAmount;
                });
            }
        });

        document.addEventListener('DOMContentLoaded', (event) => {
            const timelineItems = document.querySelectorAll('.timeline-box, .timeline-dot');
            
            // 1. Applique la classe cachée (et décalée à droite)
            timelineItems.forEach(item => {
                item.classList.add('timeline-hidden');
            });

            const animateTimeline = (entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        observer.unobserve(entry.target); 

                        timelineItems.forEach((item, index) => {
                            // Calculer le délai progressif (300ms entre chaque élément)
                            const delay = index * 300; 
                            
                            setTimeout(() => {
                                // 2. Retirer la classe 'timeline-hidden' 
                                // -> Déclenche l'animation CSS (de droite à gauche + fondu)
                                item.classList.remove('timeline-hidden');
                            }, delay);
                        });
                    }
                });
            };

            const observerOptions = {
                root: null, 
                rootMargin: '0px',
                threshold: 0.1 
            };

            const timelineSection = document.querySelector('.timeline-section');

            if (timelineSection) {
                const timelineObserver = new IntersectionObserver(animateTimeline, observerOptions);
                timelineObserver.observe(timelineSection); 
            }
        });

            document.addEventListener('DOMContentLoaded', function() {
                const urlParams = new URLSearchParams(window.location.search);
                const status = urlParams.get('status');
                const messageContainer = document.querySelector('.contact-form'); // Ciblez l'endroit où afficher le message

                if (status) {
                    let alertDiv = document.createElement('div');
                    alertDiv.classList.add('alert', 'mt-3');
                    
                    if (status === 'success') {
                        alertDiv.classList.add('alert-success');
                        alertDiv.textContent = "✅ Votre demande a été envoyée avec succès ! Nous vous répondrons très bientôt.";
                    } else if (status === 'error') {
                        alertDiv.classList.add('alert-danger');
                        const errorMsg = urlParams.get('msg') || "❌ Une erreur est survenue lors de l'envoi. Veuillez réessayer.";
                        alertDiv.textContent = errorMsg;
                    }
                    
                    // Insère le message avant le formulaire
                    messageContainer.parentNode.insertBefore(alertDiv, messageContainer);

                    // Nettoie l'URL pour la rendre propre après l'affichage du message
                    // history.replaceState(null, '', window.location.pathname);
                }
            });

 </script>


</body>

</html>
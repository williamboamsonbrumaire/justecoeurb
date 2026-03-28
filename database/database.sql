-- ===============================
-- BASE DE DONNÉES : TABLES SITE
-- ===============================

SET NAMES utf8mb4;
SET time_zone = "+00:00";

-- -------------------------------
-- TABLE info_perso
-- -------------------------------
CREATE TABLE info_perso (
    id_info_perso INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    adresse VARCHAR(255),
    telephone VARCHAR(30),
    email VARCHAR(150),
    instagram VARCHAR(255),
    facebook VARCHAR(255),
    linkedin VARCHAR(255),
    twitter VARCHAR(255),
    bio TEXT,
    cv VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------
-- TABLE citation
-- -------------------------------
CREATE TABLE citation (
    id_citation INT AUTO_INCREMENT PRIMARY KEY,
    texte TEXT NOT NULL,
    auteur VARCHAR(150),
    date_citation DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------
-- TABLE actualite
-- -------------------------------
CREATE TABLE actualite (
    id_actualite INT AUTO_INCREMENT PRIMARY KEY,
    photo VARCHAR(255),
    description TEXT NOT NULL,
    lien_article VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------
-- TABLE reflexion
-- -------------------------------
CREATE TABLE reflexion (
    id_reflexion INT AUTO_INCREMENT PRIMARY KEY,
    lien VARCHAR(255),
    courte_description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------
-- TABLE newsletter
-- -------------------------------
CREATE TABLE newsletter (
    id_newsletter INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    date_application DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_newsletter_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 
-- ============================================================
-- Structure finale complète de la table blog
-- (à utiliser si vous recréez la table from scratch)
-- ============================================================
 
 CREATE TABLE `blog` (
--   `id_blog`          INT           NOT NULL AUTO_INCREMENT,
--   `titre`            VARCHAR(255)  NOT NULL,
--   `intro`            TEXT          NOT NULL,
--   `contenu`          LONGTEXT      NOT NULL,
--   `categorie`        VARCHAR(100)  NOT NULL,
--   `photo_couverture` VARCHAR(255)  NULL DEFAULT NULL,
--   `auteur`           VARCHAR(156)  NOT NULL DEFAULT '',
--   `statut`           ENUM('brouillon','publié') NOT NULL DEFAULT 'brouillon',
--   `tags`             VARCHAR(500)  NULL DEFAULT NULL,
--   `created_at`       TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
--   `updated_at`       TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
--   PRIMARY KEY (`id_blog`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

<?php
require_once __DIR__ . '../../../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Connexion();
global $conDB;

$msg_success = '';
$msg_error   = '';

if (!isset($_SESSION['id_user'])) {
    // Redirection vers la page de login
    header('Location: ../login.php');
    exit(); // Très important : on arrête le script ici
}

// ============================================================
// FONCTION UTILITAIRE : Upload photo
// ============================================================
function uploadPhoto(array $file): array
{
    $allowed    = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ftype      = mime_content_type($file['tmp_name']);

    if (!in_array($ftype, $allowed)) {
        return ['error' => "Format d'image non autorisé (JPG, PNG, WEBP, GIF)."];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return ['error' => "L'image ne doit pas dépasser 5 Mo."];
    }

    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename   = uniqid('article_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '../../../../public/img/uploads/articles/';

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        return ['error' => "Erreur lors de l'upload de l'image."];
    }

    return ['path' => 'public/img/uploads/articles/' . $filename];
}

// ============================================================
// FONCTION UTILITAIRE : Supprimer le fichier photo du serveur
// ============================================================
function deletePhotoFile(?string $photo_path): void
{
    if ($photo_path) {
        $full_path = __DIR__ . '/../../' . $photo_path;
        if (file_exists($full_path)) unlink($full_path);
    }
}

// ============================================================
// CREATE — Ajouter un article
// ============================================================
function createArticle(PDO $conDB): array
{
    $author      = trim($_POST['author']       ?? '');
    $description = trim($_POST['description']  ?? '');
    $link        = trim($_POST['link_article'] ?? '') ?: null;

    if ($author === '' || $description === '') {
        return ['error' => "L'auteur et la description sont obligatoires."];
    }

    $photo_path = null;
    if (!empty($_FILES['photo']['name'])) {
        $upload = uploadPhoto($_FILES['photo']);
        if (isset($upload['error'])) return ['error' => $upload['error']];
        $photo_path = $upload['path'];
    }

    try {
        $stmt = $conDB->prepare(
            "INSERT INTO articles (author, photo, description, link_article)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$author, $photo_path, $description, $link]);
        return ['success' => "Publication ajoutée avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// READ — Lire tous les articles (avec pagination optionnelle)
// ============================================================
function getAllArticles(PDO $conDB, int $limit = 20, int $offset = 0): array
{
    try {
        $stmt = $conDB->prepare(
            "SELECT * FROM articles ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $limit,  PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// READ — Lire un seul article par ID
// ============================================================
function getArticleById(PDO $conDB, int $id): ?array
{
    try {
        $stmt = $conDB->prepare("SELECT * FROM articles WHERE id_article = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Exception $e) {
        return null;
    }
}

// ============================================================
// UPDATE — Modifier un article
// ============================================================
function updateArticle(PDO $conDB): array
{
    $id          = (int) ($_POST['id_article']    ?? 0);
    $author      = trim($_POST['author']          ?? '');
    $description = trim($_POST['description']     ?? '');
    $link        = trim($_POST['link_article']    ?? '') ?: null;

    if ($id <= 0) {
        return ['error' => "Article introuvable."];
    }
    if ($author === '' || $description === '') {
        return ['error' => "L'auteur et la description sont obligatoires."];
    }

    // Récupérer l'ancienne photo
    $existing = getArticleById($conDB, $id);
    if (!$existing) return ['error' => "Article introuvable."];

    $photo_path = $existing['photo']; // on garde l'ancienne par défaut

    if (!empty($_FILES['photo']['name'])) {
        $upload = uploadPhoto($_FILES['photo']);
        if (isset($upload['error'])) return ['error' => $upload['error']];

        // Supprimer l'ancienne photo du serveur
        deletePhotoFile($existing['photo']);
        $photo_path = $upload['path'];
    }

    try {
        $stmt = $conDB->prepare(
            "UPDATE articles
             SET author = ?, photo = ?, description = ?, link_article = ?
             WHERE id_article = ?"
        );
        $stmt->execute([$author, $photo_path, $description, $link, $id]);
        return ['success' => "Publication mise à jour avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DELETE — Supprimer un article
// ============================================================
function deleteArticle(PDO $conDB): array
{
    $id = (int) ($_POST['id_article'] ?? 0);

    if ($id <= 0) return ['error' => "Article introuvable."];

    $existing = getArticleById($conDB, $id);
    if (!$existing) return ['error' => "Article introuvable."];

    try {
        // Supprimer la photo du serveur avant la ligne en base
        deletePhotoFile($existing['photo']);

        $stmt = $conDB->prepare("DELETE FROM articles WHERE id_article = ?");
        $stmt->execute([$id]);
        return ['success' => "Publication supprimée avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DISPATCH — Appel automatique selon le bouton soumis
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = match(true) {
        isset($_POST['add_article'])    => createArticle($conDB),
        isset($_POST['update_article']) => updateArticle($conDB),
        isset($_POST['delete_article']) => deleteArticle($conDB),
        default                         => []
    };

    if (!empty($result['success'])) $msg_success = $result['success'];
    if (!empty($result['error']))   $msg_error   = $result['error'];
}
?>
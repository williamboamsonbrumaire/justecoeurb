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
// UTILITAIRE : Upload photo de couverture
// ============================================================
function uploadCouverture(array $file): array
{
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ftype   = mime_content_type($file['tmp_name']);

    if (!in_array($ftype, $allowed))
        return ['error' => "Format non autorisé (JPG, PNG, WEBP, GIF)."];
    if ($file['size'] > 5 * 1024 * 1024)
        return ['error' => "L'image ne doit pas dépasser 5 Mo."];

    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename   = uniqid('blog_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '../../../../public/img/uploads/blog/';

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename))
        return ['error' => "Erreur lors de l'upload."];

    return ['path' => 'public/img/uploads/blog/' . $filename];
}

// ============================================================
// UTILITAIRE : Supprimer le fichier photo
// ============================================================
function deleteCouvertureFile(?string $path): void
{
    if ($path) {
        $full = __DIR__ . 'assets/uploads/blog/' . $path;
        if (file_exists($full)) unlink($full);
    }
}

// ============================================================
// UTILITAIRE : Nettoyer les tags (virgule séparée)
// ============================================================
function sanitizeTags(string $raw): ?string
{
    $tags = array_map('trim', explode(',', $raw));
    $tags = array_filter($tags, fn($t) => $t !== '');
    $tags = array_unique($tags);
    return !empty($tags) ? implode(', ', $tags) : null;
}

// ============================================================
// CREATE — Ajouter un article de blog
// ============================================================
function createBlog(PDO $conDB): array
{
    $titre     = trim($_POST['titre']     ?? '');
    $intro     = trim($_POST['intro']     ?? '');
    $contenu   = trim($_POST['contenu']   ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $auteur    = trim($_POST['auteur']    ?? '');
    $statut    = $_POST['statut'] === 'publié' ? 'publié' : 'brouillon';
    $tags      = sanitizeTags($_POST['tags'] ?? '');

    if ($titre === '' || $intro === '' || $contenu === '' || $categorie === '' || $auteur === '')
        return ['error' => "Tous les champs obligatoires doivent être remplis."];

    $photo = null;
    if (!empty($_FILES['photo_couverture']['name'])) {
        $upload = uploadCouverture($_FILES['photo_couverture']);
        if (isset($upload['error'])) return ['error' => $upload['error']];
        $photo = $upload['path'];
    }

    try {
        $stmt = $conDB->prepare(
            "INSERT INTO blog (titre, intro, contenu, categorie, photo_couverture, auteur, statut, tags)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$titre, $intro, $contenu, $categorie, $photo, $auteur, $statut, $tags]);
        return ['success' => "Article publié avec succès !", 'id' => $conDB->lastInsertId()];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// READ — Tous les blogs avec pagination
// ============================================================
function getAllBlogs(PDO $conDB, int $limit = 6, int $offset = 0, string $statut = ''): array
{
    try {
        $where = $statut !== '' ? "WHERE statut = ?" : "";
        $stmt  = $conDB->prepare(
            "SELECT * FROM blog $where ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        if ($statut !== '') {
            $stmt->bindValue(1, $statut);
            $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
            $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(1, $limit,  PDO::PARAM_INT);
            $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// READ — Un seul blog par ID
// ============================================================
function getBlogById(PDO $conDB, int $id): ?array
{
    try {
        $stmt = $conDB->prepare("SELECT * FROM blog WHERE id_blog = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Exception $e) {
        return null;
    }
}

// ============================================================
// UPDATE — Modifier un article de blog
// ============================================================
function updateBlog(PDO $conDB): array
{
    $id        = (int) ($_POST['id_blog']    ?? 0);
    $titre     = trim($_POST['titre']        ?? '');
    $intro     = trim($_POST['intro']        ?? '');
    $contenu   = trim($_POST['contenu']      ?? '');
    $categorie = trim($_POST['categorie']    ?? '');
    $auteur    = trim($_POST['auteur']       ?? '');
    $statut    = $_POST['statut'] === 'publié' ? 'publié' : 'brouillon';
    $tags      = sanitizeTags($_POST['tags'] ?? '');

    if ($id <= 0) return ['error' => "Article introuvable."];
    if ($titre === '' || $intro === '' || $contenu === '' || $categorie === '' || $auteur === '')
        return ['error' => "Tous les champs obligatoires doivent être remplis."];

    $existing = getBlogById($conDB, $id);
    if (!$existing) return ['error' => "Article introuvable."];

    $photo = $existing['photo_couverture'];

    // Nouvelle photo uploadée
    if (!empty($_FILES['photo_couverture']['name'])) {
        $upload = uploadCouverture($_FILES['photo_couverture']);
        if (isset($upload['error'])) return ['error' => $upload['error']];
        deleteCouvertureFile($existing['photo_couverture']);
        $photo = $upload['path'];
    }

    // Supprimer la photo si demandé
    if (!empty($_POST['remove_photo'])) {
        deleteCouvertureFile($existing['photo_couverture']);
        $photo = null;
    }

    try {
        $stmt = $conDB->prepare(
            "UPDATE blog
             SET titre=?, intro=?, contenu=?, categorie=?, photo_couverture=?,
                 auteur=?, statut=?, tags=?, updated_at=NOW()
             WHERE id_blog=?"
        );
        $stmt->execute([$titre, $intro, $contenu, $categorie, $photo, $auteur, $statut, $tags, $id]);
        return ['success' => "Article mis à jour avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DELETE — Supprimer un article de blog
// ============================================================
function deleteBlog(PDO $conDB): array
{
    $id = (int) ($_POST['id_blog'] ?? 0);
    if ($id <= 0) return ['error' => "Article introuvable."];

    $existing = getBlogById($conDB, $id);
    if (!$existing) return ['error' => "Article introuvable."];

    try {
        deleteCouvertureFile($existing['photo_couverture']);
        $stmt = $conDB->prepare("DELETE FROM blog WHERE id_blog = ?");
        $stmt->execute([$id]);
        return ['success' => "Article supprimé avec succès !"];

        header('Location: ../blog.php');
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DISPATCH
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = match(true) {
        isset($_POST['add_blog'])    => createBlog($conDB),
        isset($_POST['update_blog']) => updateBlog($conDB),
        isset($_POST['delete_blog']) => deleteBlog($conDB),
        default                      => []
    };

    if (!empty($result['success'])) $msg_success = $result['success'];
    if (!empty($result['error']))   $msg_error   = $result['error'];
}
?>
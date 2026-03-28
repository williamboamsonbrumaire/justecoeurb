<?php
require_once __DIR__ . '../../../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

Connexion();
global $conDB;

if (!isset($_SESSION['id_user'])) {
    // Redirection vers la page de login
    header('Location: ../login.php');
    exit(); // Très important : on arrête le script ici
}
$msg_success = '';
$msg_error   = '';

// ============================================================
// FONCTION UTILITAIRE : Extraire l'ID YouTube depuis une URL
// ============================================================
function extractYoutubeId(string $url): ?string
{
    // Formats supportés :
    // https://www.youtube.com/watch?v=XXXXXXXXXXX
    // https://youtu.be/XXXXXXXXXXX
    // https://www.youtube.com/embed/XXXXXXXXXXX
    // https://www.youtube.com/shorts/XXXXXXXXXXX
    $patterns = [
        '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// ============================================================
// FONCTION UTILITAIRE : Valider un lien YouTube
// ============================================================
function isValidYoutubeUrl(string $url): bool
{
    return extractYoutubeId($url) !== null;
}

// ============================================================
// CREATE — Ajouter une vidéo
// ============================================================
function createVideo(PDO $conDB): array
{
    $title = trim($_POST['title_video']   ?? '');
    $link  = trim($_POST['link_youtube']  ?? '');

    if ($title === '' || $link === '') {
        return ['error' => "Le titre et le lien YouTube sont obligatoires."];
    }
    if (mb_strlen($title) > 156) {
        return ['error' => "Le titre ne doit pas dépasser 156 caractères."];
    }
    if (!isValidYoutubeUrl($link)) {
        return ['error' => "Le lien YouTube n'est pas valide."];
    }

    try {
        $stmt = $conDB->prepare(
            "INSERT INTO articles_video (title_video, link_youtube) VALUES (?, ?)"
        );
        $stmt->execute([$title, $link]);
        return ['success' => "Vidéo ajoutée avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// READ — Lire toutes les vidéos (avec pagination)
// ============================================================
function getAllVideos(PDO $conDB, int $limit = 20, int $offset = 0): array
{
    try {
        $stmt = $conDB->prepare(
            "SELECT * FROM articles_video ORDER BY created_at DESC LIMIT ? OFFSET ?"
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
// READ — Lire une seule vidéo par ID
// ============================================================
function getVideoById(PDO $conDB, int $id): ?array
{
    try {
        $stmt = $conDB->prepare("SELECT * FROM articles_video WHERE id_video = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Exception $e) {
        return null;
    }
}

// ============================================================
// UPDATE — Modifier une vidéo
// ============================================================
function updateVideo(PDO $conDB): array
{
    $id    = (int) ($_POST['id_video']      ?? 0);
    $title = trim($_POST['title_video']     ?? '');
    $link  = trim($_POST['link_youtube']    ?? '');

    if ($id <= 0)        return ['error' => "Vidéo introuvable."];
    if ($title === '')   return ['error' => "Le titre est obligatoire."];
    if ($link  === '')   return ['error' => "Le lien YouTube est obligatoire."];
    if (mb_strlen($title) > 156) return ['error' => "Le titre ne doit pas dépasser 156 caractères."];
    if (!isValidYoutubeUrl($link)) return ['error' => "Le lien YouTube n'est pas valide."];

    if (!getVideoById($conDB, $id)) return ['error' => "Vidéo introuvable."];

    try {
        $stmt = $conDB->prepare(
            "UPDATE articles_video SET title_video = ?, link_youtube = ? WHERE id_video = ?"
        );
        $stmt->execute([$title, $link, $id]);
        return ['success' => "Vidéo mise à jour avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DELETE — Supprimer une vidéo
// ============================================================
function deleteVideo(PDO $conDB): array
{
    $id = (int) ($_POST['id_video'] ?? 0);
    if ($id <= 0) return ['error' => "Vidéo introuvable."];
    if (!getVideoById($conDB, $id)) return ['error' => "Vidéo introuvable."];

    try {
        $stmt = $conDB->prepare("DELETE FROM articles_video WHERE id_video = ?");
        $stmt->execute([$id]);
        return ['success' => "Vidéo supprimée avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DISPATCH
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = match(true) {
        isset($_POST['add_video'])    => createVideo($conDB),
        isset($_POST['update_video']) => updateVideo($conDB),
        isset($_POST['delete_video']) => deleteVideo($conDB),
        default                       => []
    };

    if (!empty($result['success'])) $msg_success = $result['success'];
    if (!empty($result['error']))   $msg_error   = $result['error'];
}
?>
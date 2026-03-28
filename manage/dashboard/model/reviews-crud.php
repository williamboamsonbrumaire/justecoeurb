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
// UTILITAIRE : Upload photo du témoin
// ============================================================
function uploadReviewPhoto(array $file): array
{
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $ftype   = mime_content_type($file['tmp_name']);

    if (!in_array($ftype, $allowed))
        return ['error' => "Format non autorisé (JPG, PNG, WEBP, GIF)."];
    if ($file['size'] > 4 * 1024 * 1024)
        return ['error' => "L'image ne doit pas dépasser 4 Mo."];

    $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename   = uniqid('review_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '../../../../public/img/uploads/reviews/';

    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename))
        return ['error' => "Erreur lors de l'upload."];

    return ['path' => 'public/img/uploads/reviews/' . $filename];
}

// ============================================================
// UTILITAIRE : Supprimer le fichier photo
// ============================================================
function deleteReviewPhotoFile(?string $path): void
{
    if ($path) {
        $full = __DIR__ . '/../../../../public/' . $path;
        if (file_exists($full)) unlink($full);
    }
}

// ============================================================
// CREATE — Ajouter un témoignage
// ============================================================
function createReview(PDO $conDB): array
{
    $nom          = trim($_POST['nom']          ?? '');
    $role         = trim($_POST['role']         ?? '');
    $organisation = trim($_POST['organisation'] ?? '') ?: null;
    $quote        = trim($_POST['quote']        ?? '');
    $ordre        = max(0, (int)($_POST['ordre'] ?? 0));
    $statut       = in_array($_POST['statut'] ?? '', ['actif', 'inactif']) ? $_POST['statut'] : 'actif';

    if ($nom === '' || $role === '' || $quote === '')
        return ['error' => "Le nom, le rôle et le témoignage sont obligatoires."];

    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $upload = uploadReviewPhoto($_FILES['photo']);
        if (isset($upload['error'])) return ['error' => $upload['error']];
        $photo = $upload['path'];
    }

    try {
        $stmt = $conDB->prepare(
            "INSERT INTO reviews (nom, role, organisation, quote, photo, ordre, statut)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$nom, $role, $organisation, $quote, $photo, $ordre, $statut]);
        return ['success' => "Témoignage ajouté avec succès !", 'id' => $conDB->lastInsertId()];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// READ — Tous les témoignages
// ============================================================
function getAllReviews(PDO $conDB, string $statut = ''): array
{
    try {
        $where = $statut !== '' ? "WHERE statut = ?" : "";
        $stmt  = $conDB->prepare("SELECT * FROM reviews $where ORDER BY ordre ASC, created_at DESC");
        if ($statut !== '') {
            $stmt->execute([$statut]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// READ — Un seul témoignage par ID
// ============================================================
function getReviewById(PDO $conDB, int $id): ?array
{
    try {
        $stmt = $conDB->prepare("SELECT * FROM reviews WHERE id_review = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (Exception $e) {
        return null;
    }
}

// ============================================================
// UPDATE — Modifier un témoignage
// ============================================================
function updateReview(PDO $conDB): array
{
    $id           = (int) ($_POST['id_review']     ?? 0);
    $nom          = trim($_POST['nom']              ?? '');
    $role         = trim($_POST['role']             ?? '');
    $organisation = trim($_POST['organisation']     ?? '') ?: null;
    $quote        = trim($_POST['quote']            ?? '');
    $ordre        = max(0, (int)($_POST['ordre']    ?? 0));
    $statut       = in_array($_POST['statut'] ?? '', ['actif', 'inactif']) ? $_POST['statut'] : 'actif';

    if ($id <= 0) return ['error' => "Témoignage introuvable."];
    if ($nom === '' || $role === '' || $quote === '')
        return ['error' => "Le nom, le rôle et le témoignage sont obligatoires."];

    $existing = getReviewById($conDB, $id);
    if (!$existing) return ['error' => "Témoignage introuvable."];

    $photo = $existing['photo'];

    // Nouvelle photo uploadée
    if (!empty($_FILES['photo']['name'])) {
        $upload = uploadReviewPhoto($_FILES['photo']);
        if (isset($upload['error'])) return ['error' => $upload['error']];
        deleteReviewPhotoFile($existing['photo']);
        $photo = $upload['path'];
    }

    // Supprimer la photo si demandé
    if (!empty($_POST['remove_photo'])) {
        deleteReviewPhotoFile($existing['photo']);
        $photo = null;
    }

    try {
        $stmt = $conDB->prepare(
            "UPDATE reviews
             SET nom=?, role=?, organisation=?, quote=?, photo=?, ordre=?, statut=?, updated_at=NOW()
             WHERE id_review=?"
        );
        $stmt->execute([$nom, $role, $organisation, $quote, $photo, $ordre, $statut, $id]);
        return ['success' => "Témoignage mis à jour avec succès !"];
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// DELETE — Supprimer un témoignage
// ============================================================
function deleteReview(PDO $conDB): array
{
    $id = (int) ($_POST['id_review'] ?? 0);
    if ($id <= 0) return ['error' => "Témoignage introuvable."];

    $existing = getReviewById($conDB, $id);
    if (!$existing) return ['error' => "Témoignage introuvable."];

    try {
        deleteReviewPhotoFile($existing['photo']);
        $stmt = $conDB->prepare("DELETE FROM reviews WHERE id_review = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_success'] = "Témoignage supprimé avec succès !";
        header('Location: ../reviews.php');
        exit;
    } catch (Exception $e) {
        return ['error' => "Erreur base de données : " . $e->getMessage()];
    }
}

// ============================================================
// TOGGLE STATUT rapide (AJAX-friendly)
// ============================================================
function toggleReviewStatus(PDO $conDB): array
{
    $id = (int) ($_POST['id_review'] ?? 0);
    if ($id <= 0) return ['error' => "Témoignage introuvable."];

    try {
        $stmt = $conDB->prepare(
            "UPDATE reviews SET statut = IF(statut='actif','inactif','actif'), updated_at=NOW()
             WHERE id_review = ?"
        );
        $stmt->execute([$id]);
        return ['success' => "Statut mis à jour."];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// ============================================================
// DISPATCH
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = match(true) {
        isset($_POST['add_review'])    => createReview($conDB),
        isset($_POST['update_review']) => updateReview($conDB),
        isset($_POST['delete_review']) => deleteReview($conDB),
        isset($_POST['toggle_status']) => toggleReviewStatus($conDB),
        default                        => []
    };

    if (!empty($result['success'])) $msg_success = $result['success'];
    if (!empty($result['error']))   $msg_error   = $result['error'];
}
?>
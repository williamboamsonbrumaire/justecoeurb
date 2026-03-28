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


// ── Hashage mot de passe ──────────────────────────────────────────────────────
function hashPassword(string $pwd): string {
    return password_hash($pwd, PASSWORD_BCRYPT);
}

// ── CREATE user ───────────────────────────────────────────────────────────────
if (isset($_POST['add_user'])) {
    $name       = trim($_POST['name_user']     ?? '');
    $lastname   = trim($_POST['lastname_user'] ?? '');
    $email      = trim($_POST['email_user']    ?? '');
    $password   = trim($_POST['password_user'] ?? '');
    $role       = trim($_POST['role_user']     ?? 'editor');
    $statut     = trim($_POST['statut_user']   ?? 'actif');
    $photo      = '';

    // Upload photo
    if (!empty($_FILES['photo_user']['name'])) {
        $ext     = pathinfo($_FILES['photo_user']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $filename = 'user_' . uniqid() . '.' . $ext;
            $dest     = '../../../public/img/users/' . $filename;
            if (move_uploaded_file($_FILES['photo_user']['tmp_name'], $dest)) {
                $photo = $filename;
            }
        }
    }

    if ($name && $lastname && $email && $password) {


        $hashed = hashPassword($password);
        $stmt = $conDB->prepare("
            INSERT INTO users (name_user, lastname_user, email_user, password_user, role_user, statut_user, photo_user, created_at)
            VALUES (:name, :lastname, :email, :password, :role, :statut, :photo, NOW())
        ");
        $stmt->execute([
            ':name'     => $name,
            ':lastname' => $lastname,
            ':email'    => $email,
            ':password' => $hashed,
            ':role'     => $role,
            ':statut'   => $statut,
            ':photo'    => $photo,
        ]);
        header('Location: ../users.php?success=added');
        exit;
    }
}

// ── UPDATE user ───────────────────────────────────────────────────────────────
if (isset($_POST['update_user'])) {
    $id       = (int) ($_POST['id_user']       ?? 0);
    $name     = trim($_POST['name_user']        ?? '');
    $lastname = trim($_POST['lastname_user']    ?? '');
    $email    = trim($_POST['email_user']       ?? '');
    $role     = trim($_POST['role_user']        ?? 'editor');
    $statut   = trim($_POST['statut_user']      ?? 'actif');
    $password = trim($_POST['password_user']    ?? '');

    // Récupérer l'ancienne photo
    $old = $conDB->prepare("SELECT photo_user FROM users WHERE id_user = :id");
    $old->execute([':id' => $id]);
    $photoActuelle = $old->fetchColumn() ?: '';

    // Upload nouvelle photo
    if (!empty($_FILES['photo_user']['name'])) {
        $ext     = pathinfo($_FILES['photo_user']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $filename = 'user_' . uniqid() . '.' . $ext;
            $dest     = '../../../public/img/users/' . $filename;
            if (move_uploaded_file($_FILES['photo_user']['tmp_name'], $dest)) {
                // Supprimer l'ancienne
                if ($photoActuelle && file_exists('../../../public/img/users/' . $photoActuelle)) {
                    unlink('../../../public/img/users/' . $photoActuelle);
                }
                $photoActuelle = $filename;
            }
        }
    }

    if ($id && $name && $lastname && $email) {
        if (!empty($password)) {
            $hashed = hashPassword($password);
            $stmt = $conDB->prepare("
                UPDATE users SET name_user=:name, lastname_user=:lastname, email_user=:email,
                password_user=:password, role_user=:role, statut_user=:statut, photo_user=:photo
                WHERE id_user=:id
            ");
            $stmt->execute([
                ':name'     => $name,
                ':lastname' => $lastname,
                ':email'    => $email,
                ':password' => $hashed,
                ':role'     => $role,
                ':statut'   => $statut,
                ':photo'    => $photoActuelle,
                ':id'       => $id,
            ]);
        } else {
            $stmt = $conDB->prepare("
                UPDATE users SET name_user=:name, lastname_user=:lastname, email_user=:email,
                role_user=:role, statut_user=:statut, photo_user=:photo
                WHERE id_user=:id
            ");
            $stmt->execute([
                ':name'     => $name,
                ':lastname' => $lastname,
                ':email'    => $email,
                ':role'     => $role,
                ':statut'   => $statut,
                ':photo'    => $photoActuelle,
                ':id'       => $id,
            ]);
        }
        header('Location: ../users.php?success=updated');
        exit;
    }
}

// ── DELETE user ───────────────────────────────────────────────────────────────
if (isset($_POST['delete_user'])) {
    $id = (int) ($_POST['id_user'] ?? 0);
    if ($id) {
        // Supprimer la photo
        $old = $conDB->prepare("SELECT photo_user FROM users WHERE id_user = :id");
        $old->execute([':id' => $id]);
        $photo = $old->fetchColumn();
        if ($photo && file_exists('../../../public/img/users/' . $photo)) {
            unlink('../../../public/img/users/' . $photo);
        }

        $stmt = $conDB->prepare("DELETE FROM users WHERE id_user = :id");
        $stmt->execute([':id' => $id]);
        header('Location: ../users.php?success=deleted');
        exit;
    }
}

// ── READ : tous les users ─────────────────────────────────────────────────────
function getAllUsers(PDO $db, int $limit = 20, int $offset = 0): array {
    $stmt = $db->prepare("
        SELECT id_user, name_user, lastname_user, email_user, role_user,
               statut_user, derniere_connexion, created_at, photo_user
        FROM users
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// ── READ : un user par id ─────────────────────────────────────────────────────
function getUserById(PDO $db, int $id): array|false {
    $stmt = $db->prepare("
        SELECT id_user, name_user, lastname_user, email_user, role_user,
               statut_user, derniere_connexion, created_at, photo_user
        FROM users WHERE id_user = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
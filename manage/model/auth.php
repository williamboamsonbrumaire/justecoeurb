<?php
include '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialisation de la connexion
Connexion();
global $conDB;

$msg_error = '';
$msg_e = '';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {

        $email_log = trim($_POST['email_login'] ?? '');
        $password  = trim($_POST['pwd_login']   ?? '');

        if ($email_log === '' || $password === '') {
            $msg_e = "Veuillez remplir tous les champs.";
        } else {
            // 1. On récupère l'utilisateur par son email uniquement
            $stmt = $conDB->prepare('SELECT * FROM users WHERE email_user = ?');
            $stmt->execute([$email_log]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. On vérifie si l'utilisateur existe ET si le mot de passe est correct
            if ($user && password_verify($password, $user['password_user'])) {
                
                // 3. Enregistrement en SESSION
                $_SESSION['id_user']       = $user['id_user'];
                $_SESSION['email_user']    = $user['email_user'];
                $_SESSION['names_user']    = $user['name_user'];
                $_SESSION['lastname_user'] = $user['lastname_user'];
                $_SESSION['role_user']     = $user['role_user'];

                // 4. Redirection selon le rôle
                // On utilise strtolower pour éviter les problèmes de casse (Admin vs admin)
                switch (strtolower($user['role_user'])) {
                    case "admin":
                        header("Location: " . base_url("dashboard/index.php"));
                        exit;
                    
                    case "editor":
                    case "viewer":
                        // Ajoute ici une redirection pour les autres rôles si nécessaire
                        header("Location: " . base_url("dashboard/index.php"));
                        exit;

                    default:
                        $msg_error = "Rôle non reconnu. Veuillez contacter l'administrateur.";
                }

            } else {
                // Pour la sécurité, on affiche le même message que l'email soit faux ou le mot de passe
                $msg_error = "Email ou mot de passe incorrect.";
            }
        }
    }

} catch (Exception $e) {
    // En production, il vaut mieux loguer l'erreur et afficher un message générique
    die("Erreur système : " . $e->getMessage());
}
?>
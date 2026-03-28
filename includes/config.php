<?php
session_start();

function Connexion()
{
    global $conDB;

    try {
        $host = 'localhost'; 
        $dbname = 'jcb';
        $username = 'root';
        $password = '';
        // $host = 'localhost'; 
        // $dbname = 'u820038461_jcb';
        //  $username = 'u820038461_jcb';
        // $password = 'JCB#H@iti99!';
       

        // Construction correcte du DSN
        $connecteur = "mysql:host=$host;dbname=$dbname;charset=utf8";
        $encodage = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

        // Connexion PDO
        $conDB = new PDO($connecteur, $username, $password, $encodage);
        $conDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Forcer MySQL à utiliser le fuseau horaire d'Haïti
        $conDB->exec("SET time_zone = '-05:00'");

        // echo "Connexion réussie"; // tu peux décommenter pour tester
    } catch (Exception $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}

function verifierLien($lien)
{
    if (filter_var($lien, FILTER_VALIDATE_URL)) {
        return 0;
    } else {
        return 1;
    }
}
?>

<?php
// 🔹 Retourne le chemin absolu du serveur (pour les include/require)
function base_path($path = '') {
    return dirname(__DIR__) . '/' . ltrim($path, '/');
}

// 🔹 Retourne l'URL racine du site (Dynamique : Local ou Hostinger)
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // On récupère le dossier du projet (ex: /jcb/ ou /)
    // On nettoie pour ne garder que la partie commune avant les dossiers comme /pages ou /blog
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $dir = str_replace('\\', '/', dirname($scriptName));
    
    // Si on est dans un sous-dossier (comme /blog), on remonte à la racine réelle
    // Cette regex garde la base avant le premier dossier de script rencontré
    $baseDir = preg_replace('#/(pages|blog|includes|public).*$#', '', $dir);
    $baseDir = rtrim($baseDir, '/') . '/';

    return $protocol . $host . $baseDir . ltrim($path, '/');
}

function date_haiti($format = 'Y-m-d H:i:s', $time = 'now') {
    $dt = new DateTime($time, new DateTimeZone('America/Port-au-Prince'));
    return $dt->format($format);
}
?>


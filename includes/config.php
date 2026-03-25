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
        // $dbname = 'u626545614_visionchic';
        //  $username = 'u626545614_vchaiti';
        // $password = 'vcH@iti25#';
       

        // Construction correcte du DSN
        $connecteur = "mysql:host=$host;dbname=$dbname;charset=utf8";
        $encodage = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");

        // Connexion PDO
        $conDB = new PDO($connecteur, $username, $password, $encodage);
        $conDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conDB->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // 🔹 Forcer MySQL à utiliser le fuseau horaire d'Haïti
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
// 🔹 Retourne le chemin absolu (pour les require)
function base_path($path = '') {
    return __DIR__ . '/' . ltrim($path, '/');
}

// 🔹 Retourne l'URL de base du site (automatique selon environnement)
function base_url($path = '') {
    // Exemple : https://visionchic.hostingerapp.com/
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // Pour Hostinger, ton site est directement dans public_html
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

    return $protocol . $host . $base . '/' . ltrim($path, '/');
}

function date_haiti($format = 'Y-m-d H:i:s', $time = 'now') {
    $dt = new DateTime($time, new DateTimeZone('America/Port-au-Prince'));
    return $dt->format($format);
}


?>


<?php
session_start();

// Configuration de la base de données
/*define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacie_garde');
define('DB_USER', 'root');
define('DB_PASS', '');*/

// Connexion à la base de données avec PDO
try {
    $pdo = new PDO('mysql:host=localhost;dbname=pharmacie_garde;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Configuration des chemins
define('ROOT_DIR', dirname(__DIR__));
define('PUBLIC_DIR', ROOT_DIR . '/public');

// Configuration de la base URL
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Correction spécifique pour WAMP
$project_dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_DIR);
$project_dir = str_replace('\\', '/', $project_dir); // Pour Windows

define('BASE_URL', $protocol . $host . $project_dir);

// Fonction pour sécuriser les données
/*function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}*/
?>
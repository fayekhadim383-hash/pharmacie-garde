<?php
// Démarrage de la session
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Détruire toutes les données de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, effacez également
// le cookie de session.
// Note : cela détruira la session et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalement, on détruit la session
session_destroy();

// Redirection vers la page d'accueil avec un message
$_SESSION['message'] = "Vous avez été déconnecté avec succès.";
header("Location: login.php");
exit();
?>
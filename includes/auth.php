<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Vérifie les identifiants de connexion (version debug)
 */
function verifyCredentials($pdo, $username, $password) {
    // Debug: Afficher les entrées
    error_log("Tentative de connexion - Username: ".$username);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Debug: Afficher l'utilisateur trouvé
        error_log("Utilisateur trouvé: ".print_r($user, true));
        
        if ($user) {
            // Debug: Comparaison des mots de passe
            error_log("Mot de passe fourni: ".$password);
            error_log("Mot de passe stocké: ".$user['password']);
            error_log("Résultat vérification: ".(password_verify($password, $user['password']) ? 'OK' : 'ÉCHEC'));
            
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erreur DB: ".$e->getMessage());
        return false;
    }
}

function requireAdmin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }
    
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur est admin
 */
function isAdmin() {
    return isLoggedIn() && ($_SESSION['role'] === 'admin');
}

?>
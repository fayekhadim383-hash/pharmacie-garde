<?php
require_once 'config.php';

/**
 * Nettoie et sécurise les données entrantes
 * @param string $data La donnée à nettoyer
 * @return string La donnée nettoyée
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
/**
 * Redirige vers une URL avec un message flash
 * @param string $url L'URL de destination
 * @param string $type Le type de message (success, error, etc.)
 * @param string $message Le message à afficher
 */
function redirect($url, $type = null, $message = null) {
    if ($type && $message) {
        $_SESSION[$type] = $message;
    }
    header("Location: $url");
    exit();
}


/**
 * Génère une URL absolue pour l'application
 * @param string $path Chemin relatif
 * @return string URL complète
 */
function app_url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}

/**
 * Vérifie si l'URL courante correspond au lien
 * @param string $path Chemin à comparer
 * @return bool True si c'est la page active
 */
function is_active($path) {
    $current = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return strpos($current, $path) !== false;
}




/**
 * Affiche un message flash
 * @param string $type Le type de message
 * @return string Le message HTML ou une chaîne vide
 */
function flash($type) {
    if (isset($_SESSION[$type])) {
        $message = $_SESSION[$type];
        unset($_SESSION[$type]);
        
        $class = 'alert ';
        switch ($type) {
            case 'success':
                $class .= 'alert-success';
                break;
            case 'error':
                $class .= 'alert-danger';
                break;
            case 'warning':
                $class .= 'alert-warning';
                break;
            case 'info':
                $class .= 'alert-info';
                break;
            default:
                $class .= 'alert-primary';
        }
        
        return '<div class="'.$class.'">'.$message.'</div>';
    }
    return '';
}

/**
 * Formate un numéro de téléphone
 * @param string $phone Le numéro de téléphone
 * @return string Le numéro formaté
 */
function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 10) {
        return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $phone);
    }
    return $phone;
}

/**
 * Vérifie si une pharmacie est de garde à une date donnée
 * @param int $pharmacie_id L'ID de la pharmacie
 * @param string $date La date à vérifier (format YYYY-MM-DD)
 * @return bool True si la pharmacie est de garde, false sinon
 */
function isPharmacieEnGarde($pharmacie_id, $date = null) {
    global $pdo;
    
    $date = $date ?: date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM gardes 
        WHERE pharmacie_id = ? 
        AND date_debut <= ? 
        AND date_fin >= ?
    ");
    $stmt->execute([$pharmacie_id, $date, $date]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Récupère les pharmacies de garde pour une période donnée
 * @param string $start_date Date de début (format YYYY-MM-DD)
 * @param string $end_date Date de fin (format YYYY-MM-DD)
 * @return array Tableau des pharmacies de garde
 */
function getPharmaciesDeGarde($start_date = null, $end_date = null) {
    global $pdo;
    
    $start_date = $start_date ?: date('Y-m-d');
    $end_date = $end_date ?: date('Y-m-d');
    
    $stmt = $pdo->prepare("
        SELECT p.*, g.date_debut, g.date_fin, z.nom as zone_nom
        FROM gardes g
        JOIN pharmacies p ON g.pharmacie_id = p.id
        LEFT JOIN zones z ON p.zone_id = z.id
        WHERE g.date_debut <= ? AND g.date_fin >= ?
        ORDER BY g.date_debut, p.nom
    ");
    $stmt->execute([$end_date, $start_date]);
    
    return $stmt->fetchAll();
}

/**
 * Génère un mot de passe hashé
 * @param string $password Le mot de passe en clair
 * @return string Le mot de passe hashé
 */
function generateHash($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Valide une adresse email
 * @param string $email L'adresse email à valider
 * @return bool True si l'email est valide, false sinon
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Convertit une date du format MySQL au format français
 * @param string $date La date au format MySQL (YYYY-MM-DD)
 * @return string La date au format français (DD/MM/YYYY)
 */
function dateToFrench($date) {
    if (empty($date) || $date === '0000-00-00') return '';
    return date('d/m/Y', strtotime($date));
}

/**
 * Vérifie si un fichier uploadé est une image valide
 * @param array $file Le fichier $_FILES
 * @return array Tableau avec 'success' et 'message'
 */
function validateImageUpload($file) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erreur lors du téléchargement'];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Type de fichier non autorisé'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Fichier trop volumineux (max 2MB)'];
    }
    
    return ['success' => true, 'message' => 'Fichier valide'];
}

/**
 * Génère un slug à partir d'une chaîne
 * @param string $string La chaîne à convertir
 * @return string Le slug généré
 */
function generateSlug($string) {
    $string = preg_replace('/[^a-zA-Z0-9 \-\_]/', '', $string);
    $string = strtolower($string);
    $string = str_replace(' ', '-', $string);
    $string = preg_replace('/\-+/', '-', $string);
    return $string;
}

/**
 * Récupère les statistiques globales
 * @return array Tableau des statistiques
 */
function getStats() {
    global $pdo;
    
    $stats = [];
    
    // Nombre total de pharmacies
    $stmt = $pdo->query("SELECT COUNT(*) FROM pharmacies");
    $stats['total_pharmacies'] = $stmt->fetchColumn();
    
    // Nombre total de zones
    $stmt = $pdo->query("SELECT COUNT(*) FROM zones");
    $stats['total_zones'] = $stmt->fetchColumn();
    
    // Pharmacies de garde aujourd'hui
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM gardes 
        WHERE date_debut <= CURDATE() 
        AND date_fin >= CURDATE()
    ");
    $stats['pharmacies_garde'] = $stmt->fetchColumn();
    
    return $stats;
}
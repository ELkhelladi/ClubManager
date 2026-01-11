<?php
/**
 * Configuration du backend
 */

// Chemins des fichiers
define('XML_DIR', '../xml/');
define('DEMANDES_XML', XML_DIR . 'demandes.xml');
define('CLUBS_XML', XML_DIR . 'clubs.xml');
define('USERS_XML', XML_DIR . 'users.xml');

// Configuration CORS
define('ALLOW_ORIGIN', '*'); // En production, mettez votre domaine

// Fuseau horaire
date_default_timezone_set('Africa/Casablanca');

// Fonction utilitaire pour les headers
function setCorsHeaders() {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: ' . ALLOW_ORIGIN);
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Fonction pour échapper les caractères XML
function escapeXML($str) {
    return htmlspecialchars($str, ENT_XML1, 'UTF-8');
}

// Fonction pour créer le dossier XML s'il n'existe pas
function ensureXmlDirExists() {
    if (!file_exists(XML_DIR)) {
        if (!mkdir(XML_DIR, 0777, true)) {
            throw new Exception("Impossible de créer le dossier " . XML_DIR);
        }
    }
    
    if (!is_writable(XML_DIR)) {
        @chmod(XML_DIR, 0777);
        if (!is_writable(XML_DIR)) {
            throw new Exception("Le dossier " . XML_DIR . " n'est pas accessible en écriture");
        }
    }
}

// Fonction pour logger les erreurs
function logError($message, $context = []) {
    $log = date('Y-m-d H:i:s') . " - " . $message;
    if (!empty($context)) {
        $log .= " - Context: " . json_encode($context);
    }
    error_log($log);
}
?>
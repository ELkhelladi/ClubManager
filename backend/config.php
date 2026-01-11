<?php
/**
 * Configuration du backend - Club Informatique
 */

// Désactiver l'affichage des erreurs en production (pour retourner du JSON propre)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log des erreurs dans un fichier
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-errors.log');

// Chemins des fichiers XML (depuis le dossier backend/)
define('XML_DIR', dirname(__DIR__) . '/xml/');
define('CLUBS_XML', XML_DIR . 'clubs.xml');
define('MEMBRES_XML', XML_DIR . 'membres.xml');
define('USERS_XML', XML_DIR . 'users.xml');
define('ACTIVITES_XML', XML_DIR . 'activites.xml');
define('PARTICIPATIONS_XML', XML_DIR . 'participations.xml');

// Configuration
date_default_timezone_set('Africa/Casablanca');

/**
 * S'assurer que le dossier XML existe et est accessible en écriture
 */
function ensureXmlDirExists() {
    // Vérifier si le dossier existe
    if (!file_exists(XML_DIR)) {
        if (!@mkdir(XML_DIR, 0777, true)) {
            logError("Impossible de créer le dossier XML", ['path' => XML_DIR]);
            throw new Exception("Impossible de créer le dossier XML");
        }
    }
    
    // Vérifier les permissions
    if (!is_writable(XML_DIR)) {
        @chmod(XML_DIR, 0777);
        if (!is_writable(XML_DIR)) {
            logError("Dossier XML non accessible en écriture", ['path' => XML_DIR]);
            throw new Exception("Le dossier XML n'est pas accessible en écriture");
        }
    }
    
    // Créer membres.xml s'il n'existe pas
    if (!file_exists(MEMBRES_XML)) {
        try {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = true;
            $root = $xml->createElement('membres');
            $xml->appendChild($root);
            
            if (!$xml->save(MEMBRES_XML)) {
                throw new Exception("Impossible de créer membres.xml");
            }
            
            @chmod(MEMBRES_XML, 0666);
            logError("Fichier membres.xml créé", ['path' => MEMBRES_XML]);
        } catch (Exception $e) {
            logError("Erreur création membres.xml", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Créer activites.xml s'il n'existe pas
    if (!file_exists(ACTIVITES_XML)) {
        try {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = true;
            $root = $xml->createElement('activites');
            $xml->appendChild($root);
            
            if (!$xml->save(ACTIVITES_XML)) {
                throw new Exception("Impossible de créer activites.xml");
            }
            
            @chmod(ACTIVITES_XML, 0666);
            logError("Fichier activites.xml créé", ['path' => ACTIVITES_XML]);
        } catch (Exception $e) {
            logError("Erreur création activites.xml", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    // Créer participations.xml s'il n'existe pas
    if (!file_exists(PARTICIPATIONS_XML)) {
        try {
            $xml = new DOMDocument('1.0', 'UTF-8');
            $xml->formatOutput = true;
            $root = $xml->createElement('participations');
            $xml->appendChild($root);
            
            if (!$xml->save(PARTICIPATIONS_XML)) {
                throw new Exception("Impossible de créer participations.xml");
            }
            
            @chmod(PARTICIPATIONS_XML, 0666);
            logError("Fichier participations.xml créé", ['path' => PARTICIPATIONS_XML]);
        } catch (Exception $e) {
            logError("Erreur création participations.xml", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

/**
 * Logger les erreurs dans un fichier
 */
function logError($message, $data = []) {
    $logFile = __DIR__ . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}";
    
    if (!empty($data)) {
        $logMessage .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    $logMessage .= "\n";
    
    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Échapper les caractères spéciaux pour XML
 */
function escapeXML($str) {
    if ($str === null || $str === '') {
        return '';
    }
    return htmlspecialchars(trim($str), ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/**
 * Retourner une réponse JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Retourner une erreur JSON
 */
function jsonError($message, $statusCode = 500, $data = []) {
    logError("Erreur API: " . $message, $data);
    jsonResponse([
        'success' => false,
        'message' => $message,
        'data' => $data
    ], $statusCode);
}
?>
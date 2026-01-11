<?php
// Headers CORS d'abord (avant tout output)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Désactiver l'affichage des erreurs
ini_set('display_errors', 0);

// Charger la configuration
require_once __DIR__ . '/config.php';

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

// Capturer toutes les erreurs PHP
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError("Erreur PHP", [
        'errno' => $errno,
        'errstr' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    
    jsonError("Erreur serveur: " . $errstr, 500);
});

// Capturer les exceptions non gérées
set_exception_handler(function($exception) {
    logError("Exception non gérée", [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    
    jsonError($exception->getMessage(), 500);
});

try {
    $action = $_GET['action'] ?? '';

    switch($action) {
        case 'ajouter':
            ajouterMembre();
            break;
        case 'modifier':
            modifierMembre();
            break;
        case 'supprimer':
            supprimerMembre();
            break;
        case 'lister':
            listerMembres();
            break;
        default:
            jsonError('Action non valide: ' . $action, 400);
    }
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}

function ajouterMembre() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        ensureXmlDirExists();
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['nom']) || empty($data['email'])) {
            throw new Exception("Nom et email requis");
        }

        $xmlFile = MEMBRES_XML;
        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
            if (!$xml) {
                throw new Exception("Impossible de charger membres.xml");
            }
        } else {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><membres></membres>');
        }

        // Vérifier si l'email existe déjà
        foreach ($xml->membre as $m) {
            if ((string)$m->email === $data['email']) {
                throw new Exception("Un membre avec cet email existe déjà");
            }
        }

        $membre = $xml->addChild('membre');
        $membre->addAttribute('id', 'M' . time() . rand(100, 999));
        $membre->addChild('nom', escapeXML($data['nom']));
        $membre->addChild('email', escapeXML($data['email']));
        $membre->addChild('telephone', escapeXML($data['telephone'] ?? ''));
        $membre->addChild('dateAdhesion', date('Y-m-d H:i:s'));

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if (!$dom->save($xmlFile)) {
            throw new Exception("Impossible de sauvegarder membres.xml");
        }

        @chmod($xmlFile, 0666);

        echo json_encode([
            'success' => true,
            'message' => 'Membre ajouté avec succès'
        ]);

    } catch (Exception $e) {
        logError("Erreur ajout membre", ['error' => $e->getMessage()]);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function modifierMembre() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['id'])) {
            throw new Exception("ID membre manquant");
        }

        $xmlFile = MEMBRES_XML;
        if (!file_exists($xmlFile)) {
            throw new Exception("Fichier membres.xml introuvable");
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger membres.xml");
        }

        $trouve = false;
        foreach ($xml->membre as $membre) {
            if ((string)$membre['id'] === $data['id']) {
                $membre->nom = escapeXML($data['nom']);
                $membre->email = escapeXML($data['email']);
                $membre->telephone = escapeXML($data['telephone'] ?? '');
                $trouve = true;
                break;
            }
        }

        if (!$trouve) {
            throw new Exception("Membre introuvable");
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if (!$dom->save($xmlFile)) {
            throw new Exception("Impossible de sauvegarder");
        }

        echo json_encode([
            'success' => true,
            'message' => 'Membre modifié avec succès'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function supprimerMembre() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['id'])) {
            throw new Exception("ID membre manquant");
        }

        $xmlFile = MEMBRES_XML;
        if (!file_exists($xmlFile)) {
            throw new Exception("Fichier membres.xml introuvable");
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger membres.xml");
        }

        $trouve = false;
        $index = 0;
        foreach ($xml->membre as $membre) {
            if ((string)$membre['id'] === $data['id']) {
                unset($xml->membre[$index]);
                $trouve = true;
                break;
            }
            $index++;
        }

        if (!$trouve) {
            throw new Exception("Membre introuvable");
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if (!$dom->save($xmlFile)) {
            throw new Exception("Impossible de sauvegarder");
        }

        echo json_encode([
            'success' => true,
            'message' => 'Membre supprimé avec succès'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function listerMembres() {
    try {
        ensureXmlDirExists();
        
        $xmlFile = MEMBRES_XML;
        
        if (!file_exists($xmlFile)) {
            echo json_encode([
                'success' => true,
                'membres' => []
            ]);
            return;
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger membres.xml");
        }

        $membres = [];
        foreach ($xml->membre as $membre) {
            $membres[] = [
                'id' => (string)$membre['id'],
                'nom' => (string)$membre->nom,
                'email' => (string)$membre->email,
                'telephone' => (string)$membre->telephone,
                'dateAdhesion' => (string)$membre->dateAdhesion
            ];
        }

        echo json_encode([
            'success' => true,
            'membres' => $membres
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
?>
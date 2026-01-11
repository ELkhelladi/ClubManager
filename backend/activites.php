<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'ajouter':
        ajouterActivite();
        break;
    case 'modifier':
        modifierActivite();
        break;
    case 'supprimer':
        supprimerActivite();
        break;
    case 'lister':
        listerActivites();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}

function ajouterActivite() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        ensureXmlDirExists();
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['titre']) || empty($data['date'])) {
            throw new Exception("Titre et date requis");
        }

        $xmlFile = ACTIVITES_XML;
        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
            if (!$xml) {
                throw new Exception("Impossible de charger activites.xml");
            }
        } else {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><activites></activites>');
        }

        $activite = $xml->addChild('activite');
        $activite->addAttribute('id', 'A' . time() . rand(100, 999));
        $activite->addChild('titre', escapeXML($data['titre']));
        $activite->addChild('description', escapeXML($data['description'] ?? ''));
        $activite->addChild('date', $data['date']);
        $activite->addChild('lieu', escapeXML($data['lieu'] ?? ''));
        $activite->addChild('placesMax', $data['placesMax'] ?? 30);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if (!$dom->save($xmlFile)) {
            throw new Exception("Impossible de sauvegarder activites.xml");
        }

        @chmod($xmlFile, 0666);

        echo json_encode([
            'success' => true,
            'message' => 'Activité créée avec succès'
        ]);

    } catch (Exception $e) {
        logError("Erreur ajout activité", ['error' => $e->getMessage()]);
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function modifierActivite() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['id'])) {
            throw new Exception("ID activité manquant");
        }

        $xmlFile = ACTIVITES_XML;
        if (!file_exists($xmlFile)) {
            throw new Exception("Fichier activites.xml introuvable");
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger activites.xml");
        }

        $trouve = false;
        foreach ($xml->activite as $activite) {
            if ((string)$activite['id'] === $data['id']) {
                $activite->titre = escapeXML($data['titre']);
                $activite->description = escapeXML($data['description'] ?? '');
                $activite->date = $data['date'];
                $activite->lieu = escapeXML($data['lieu'] ?? '');
                $activite->placesMax = $data['placesMax'] ?? 30;
                $trouve = true;
                break;
            }
        }

        if (!$trouve) {
            throw new Exception("Activité introuvable");
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
            'message' => 'Activité modifiée avec succès'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function supprimerActivite() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['id'])) {
            throw new Exception("ID activité manquant");
        }

        $xmlFile = ACTIVITES_XML;
        if (!file_exists($xmlFile)) {
            throw new Exception("Fichier activites.xml introuvable");
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger activites.xml");
        }

        $trouve = false;
        $index = 0;
        foreach ($xml->activite as $activite) {
            if ((string)$activite['id'] === $data['id']) {
                unset($xml->activite[$index]);
                $trouve = true;
                break;
            }
            $index++;
        }

        if (!$trouve) {
            throw new Exception("Activité introuvable");
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
            'message' => 'Activité supprimée avec succès'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function listerActivites() {
    try {
        ensureXmlDirExists();
        
        $xmlFile = ACTIVITES_XML;
        
        if (!file_exists($xmlFile)) {
            echo json_encode([
                'success' => true,
                'activites' => []
            ]);
            return;
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger activites.xml");
        }

        $activites = [];
        foreach ($xml->activite as $activite) {
            $activites[] = [
                'id' => (string)$activite['id'],
                'titre' => (string)$activite->titre,
                'description' => (string)$activite->description,
                'date' => (string)$activite->date,
                'lieu' => (string)$activite->lieu,
                'placesMax' => (int)$activite->placesMax
            ];
        }

        echo json_encode([
            'success' => true,
            'activites' => $activites
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
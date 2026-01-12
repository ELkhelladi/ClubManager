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
    case 'rejoindre':
        rejoindreActivite();
        break;
    case 'annuler':
        annulerParticipation();
        break;
    case 'lister':
        listerParticipations();
        break;
    case 'mesActivites':
        mesActivites();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
}

function rejoindreActivite() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        ensureXmlDirExists();
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['activiteId']) || empty($data['membreNom'])) {
            throw new Exception("Données invalides");
        }

        $xmlFile = PARTICIPATIONS_XML;
        if (file_exists($xmlFile)) {
            $xml = simplexml_load_file($xmlFile);
            if (!$xml) {
                throw new Exception("Impossible de charger participations.xml");
            }
        } else {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><participations></participations>');
        }

        // Vérifier si déjà inscrit
        foreach ($xml->participation as $p) {
            if ((string)$p->activiteId === $data['activiteId'] && 
                (string)$p->membreNom === $data['membreNom']) {
                throw new Exception("Vous êtes déjà inscrit à cette activité");
            }
        }

        // Compter les participants
        $count = 0;
        foreach ($xml->participation as $p) {
            if ((string)$p->activiteId === $data['activiteId']) {
                $count++;
            }
        }

        // Vérifier places disponibles
        $activitesXml = simplexml_load_file(ACTIVITES_XML);
        $placesMax = 30;
        foreach ($activitesXml->activite as $act) {
            if ((string)$act['id'] === $data['activiteId']) {
                $placesMax = (int)$act->placesMax;
                break;
            }
        }

        if ($count >= $placesMax) {
            throw new Exception("Plus de places disponibles pour cette activité");
        }

        $participation = $xml->addChild('participation');
        $participation->addAttribute('id', 'P' . time() . rand(100, 999));
        $participation->addChild('activiteId', $data['activiteId']);
        $participation->addChild('membreNom', htmlspecialchars($data['membreNom']));
        $participation->addChild('dateInscription', date('Y-m-d H:i:s'));

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if (!$dom->save($xmlFile)) {
            throw new Exception("Impossible de sauvegarder participations.xml");
        }

        @chmod($xmlFile, 0666);

        echo json_encode([
            'success' => true,
            'message' => 'Inscription réussie !'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function annulerParticipation() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        return;
    }

    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || empty($data['activiteId']) || empty($data['membreNom'])) {
            throw new Exception("Données invalides");
        }

        $xmlFile = PARTICIPATIONS_XML;
        if (!file_exists($xmlFile)) {
            throw new Exception("Aucune participation trouvée");
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger participations.xml");
        }

        $trouve = false;
        $index = 0;
        foreach ($xml->participation as $p) {
            if ((string)$p->activiteId === $data['activiteId'] && 
                (string)$p->membreNom === $data['membreNom']) {
                unset($xml->participation[$index]);
                $trouve = true;
                break;
            }
            $index++;
        }

        if (!$trouve) {
            throw new Exception("Participation introuvable");
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if (!$dom->save($xmlFile)) {
            throw new Exception("Impossible de sauvegarder les modifications");
        }

        echo json_encode([
            'success' => true,
            'message' => 'Participation annulée'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function listerParticipations() {
    try {
        $activiteId = $_GET['activiteId'] ?? '';
        
        if (empty($activiteId)) {
            throw new Exception("ID activité manquant");
        }

        $xmlFile = PARTICIPATIONS_XML;
        
        if (!file_exists($xmlFile)) {
            echo json_encode([
                'success' => true,
                'participations' => [],
                'count' => 0
            ]);
            return;
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger participations.xml");
        }

        $participations = [];
        foreach ($xml->participation as $p) {
            if ((string)$p->activiteId === $activiteId) {
                $participations[] = [
                    'id' => (string)$p['id'],
                    'membreNom' => (string)$p->membreNom,
                    'dateInscription' => (string)$p->dateInscription
                ];
            }
        }

        echo json_encode([
            'success' => true,
            'participations' => $participations,
            'count' => count($participations)
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function mesActivites() {
    try {
        $membreNom = $_GET['membreNom'] ?? '';
        
        if (empty($membreNom)) {
            throw new Exception("Nom membre manquant");
        }

        $xmlFile = PARTICIPATIONS_XML;
        
        if (!file_exists($xmlFile)) {
            echo json_encode([
                'success' => true,
                'activiteIds' => []
            ]);
            return;
        }

        $xml = simplexml_load_file($xmlFile);
        if (!$xml) {
            throw new Exception("Impossible de charger participations.xml");
        }

        $activiteIds = [];
        foreach ($xml->participation as $p) {
            if ((string)$p->membreNom === $membreNom) {
                $activiteIds[] = (string)$p->activiteId;
            }
        }

        echo json_encode([
            'success' => true,
            'activiteIds' => $activiteIds
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
<?php
// traiter_demande.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (!$data) {
            throw new Exception("Données invalides");
        }
        
        $demandeId = $data['id'];
        $action = $data['action']; // 'accepter' ou 'rejeter'
        
        $xmlFile = '../xml/demandes.xml';
        
        if (!file_exists($xmlFile)) {
            throw new Exception("Fichier demandes.xml introuvable");
        }
        
        $xml = simplexml_load_file($xmlFile);
        
        if (!$xml) {
            throw new Exception("Impossible de charger demandes.xml");
        }
        
        // Trouver et modifier la demande
        $trouve = false;
        foreach ($xml->demande as $demande) {
            if ((string)$demande['id'] === $demandeId) {
                $nouveauStatut = ($action === 'accepter') ? 'acceptee' : 'rejetee';
                $demande->statut = $nouveauStatut;
                $demande->addChild('dateTraitement', date('c'));
                $trouve = true;
                break;
            }
        }
        
        if (!$trouve) {
            throw new Exception("Demande ID $demandeId introuvable");
        }
        
        // Sauvegarder
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        $dom->save($xmlFile);
        
        echo json_encode([
            'success' => true,
            'message' => 'Demande traitée avec succès',
            'statut' => $nouveauStatut
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>
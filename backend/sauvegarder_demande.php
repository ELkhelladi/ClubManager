<?php
// Charger la configuration
require_once 'config.php';

// Headers CORS
setCorsHeaders();

// Gérer les requêtes OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // S'assurer que le dossier XML existe
        ensureXmlDirExists();
        
        // Lire les données envoyées
        $json = file_get_contents('php://input');
        logError("Demande reçue", ['json_length' => strlen($json)]);
        
        $data = json_decode($json, true);
        
        if (!$data) {
            throw new Exception("Données JSON invalides");
        }
        
        // Valider les données requises
        $required = ['id', 'nomEtudiant', 'clubId', 'dateCreation'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Champ requis manquant: $field");
            }
        }
        
        // Charger ou créer le XML
        if (file_exists(DEMANDES_XML)) {
            $xml = simplexml_load_file(DEMANDES_XML);
            if (!$xml) {
                throw new Exception("Impossible de charger demandes.xml");
            }
        } else {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><demandes></demandes>');
        }
        
        // Vérifier si la demande existe déjà
        foreach ($xml->demande as $d) {
            if ((string)$d['id'] === $data['id']) {
                throw new Exception("Une demande avec cet ID existe déjà");
            }
        }
        
        // Ajouter la nouvelle demande
        $demande = $xml->addChild('demande');
        $demande->addAttribute('id', $data['id']);
        $demande->addChild('nomEtudiant', escapeXML($data['nomEtudiant']));
        $demande->addChild('email', escapeXML($data['email'] ?? ''));
        $demande->addChild('motivation', escapeXML($data['motivation'] ?? ''));
        $demande->addChild('clubId', escapeXML($data['clubId']));
        $demande->addChild('statut', 'en_attente');
        $demande->addChild('dateCreation', $data['dateCreation']);
        
        // Formater et sauvegarder
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        // Vérifier les permissions avant d'écrire
        if (file_exists(DEMANDES_XML) && !is_writable(DEMANDES_XML)) {
            @chmod(DEMANDES_XML, 0666);
            if (!is_writable(DEMANDES_XML)) {
                throw new Exception("Le fichier demandes.xml n'est pas accessible en écriture");
            }
        }
        
        $saved = $dom->save(DEMANDES_XML);
        
        if ($saved === false) {
            throw new Exception("Impossible d'écrire dans demandes.xml");
        }
        
        // S'assurer que le fichier est lisible
        @chmod(DEMANDES_XML, 0666);
        
        logError("Demande sauvegardée avec succès", ['id' => $data['id']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Demande ajoutée avec succès dans demandes.xml',
            'id' => $data['id'],
            'file' => DEMANDES_XML
        ]);
        
    } catch (Exception $e) {
        logError("ERREUR lors de la sauvegarde", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
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
        'message' => 'Méthode non autorisée. Utilisez POST.'
    ]);
}
?>
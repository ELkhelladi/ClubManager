<?php
/**
 * API pour la gestion des clubs (CRUD)
 */

// require_once 'config.php';
// setCorsHeaders();

// // Gérer OPTIONS
// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     http_response_code(200);
//     exit;
// }

// // Récupérer l'action
// $action = $_GET['action'] ?? '';

// // Route selon l'action
// switch ($action) {
//     case 'ajouter':
//         ajouterClub();
//         break;
//     case 'modifier':
//         modifierClub();
//         break;
//     case 'supprimer':
//         supprimerClub();
//         break;
//     case 'lister':
//         listerClubs();
//         break;
//     default:
//         http_response_code(400);
//         echo json_encode([
//             'success' => false,
//             'message' => 'Action non valide. Utilisez: ajouter, modifier, supprimer, lister'
//         ]);
// }

// // =============================================
// // AJOUTER UN CLUB
// // =============================================

// function ajouterClub() {
//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         http_response_code(405);
//         echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
//         return;
//     }

//     try {
//         ensureXmlDirExists();
        
//         $json = file_get_contents('php://input');
//         $data = json_decode($json, true);
        
//         if (!$data) {
//             throw new Exception("Données JSON invalides");
//         }
        
//         // Valider les données
//         $required = ['id', 'nomFr', 'nomEn', 'descFr', 'descEn'];
//         foreach ($required as $field) {
//             if (empty($data[$field])) {
//                 throw new Exception("Champ requis manquant: $field");
//             }
//         }
        
//         // Charger ou créer le XML
//         $xmlFile = CLUBS_XML;
//         if (file_exists($xmlFile)) {
//             $xml = simplexml_load_file($xmlFile);
//             if (!$xml) {
//                 throw new Exception("Impossible de charger clubs.xml");
//             }
//         } else {
//             $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><clubs></clubs>');
//         }
        
//         // Vérifier si l'ID existe déjà
//         foreach ($xml->club as $c) {
//             if ((string)$c['id'] === $data['id']) {
//                 throw new Exception("Un club avec cet ID existe déjà");
//             }
//         }
        
//         // Ajouter le nouveau club
//         $club = $xml->addChild('club');
//         $club->addAttribute('id', $data['id']);
        
//         $nom = $club->addChild('nom');
//         $nom->addAttribute('fr', $data['nomFr']);
//         $nom->addAttribute('en', $data['nomEn']);
        
//         $desc = $club->addChild('description');
//         $desc->addAttribute('fr', $data['descFr']);
//         $desc->addAttribute('en', $data['descEn']);
        
//         // Sauvegarder
//         $dom = new DOMDocument('1.0', 'UTF-8');
//         $dom->preserveWhiteSpace = false;
//         $dom->formatOutput = true;
//         $dom->loadXML($xml->asXML());
        
//         if (!$dom->save($xmlFile)) {
//             throw new Exception("Impossible de sauvegarder clubs.xml");
//         }
        
//         @chmod($xmlFile, 0666);
        
//         logError("Club ajouté avec succès", ['id' => $data['id']]);
        
//         echo json_encode([
//             'success' => true,
//             'message' => 'Club ajouté avec succès',
//             'club' => $data
//         ]);
        
//     } catch (Exception $e) {
//         logError("Erreur ajout club", ['error' => $e->getMessage()]);
//         http_response_code(500);
//         echo json_encode([
//             'success' => false,
//             'message' => $e->getMessage()
//         ]);
//     }
// }

// // =============================================
// // MODIFIER UN CLUB
// // =============================================

// function modifierClub() {
//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         http_response_code(405);
//         echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
//         return;
//     }

//     try {
//         $json = file_get_contents('php://input');
//         $data = json_decode($json, true);
        
//         if (!$data || empty($data['id'])) {
//             throw new Exception("Données invalides");
//         }
        
//         $xmlFile = CLUBS_XML;
//         if (!file_exists($xmlFile)) {
//             throw new Exception("Fichier clubs.xml introuvable");
//         }
        
//         $xml = simplexml_load_file($xmlFile);
//         if (!$xml) {
//             throw new Exception("Impossible de charger clubs.xml");
//         }
        
//         // Trouver et modifier le club
//         $trouve = false;
//         foreach ($xml->club as $club) {
//             if ((string)$club['id'] === $data['id']) {
//                 // Modifier le nom
//                 $nom = $club->nom;
//                 $nom['fr'] = $data['nomFr'];
//                 $nom['en'] = $data['nomEn'];
                
//                 // Modifier la description
//                 $desc = $club->description;
//                 $desc['fr'] = $data['descFr'];
//                 $desc['en'] = $data['descEn'];
                
//                 $trouve = true;
//                 break;
//             }
//         }
        
//         if (!$trouve) {
//             throw new Exception("Club avec ID {$data['id']} introuvable");
//         }
        
//         // Sauvegarder
//         $dom = new DOMDocument('1.0', 'UTF-8');
//         $dom->preserveWhiteSpace = false;
//         $dom->formatOutput = true;
//         $dom->loadXML($xml->asXML());
        
//         if (!$dom->save($xmlFile)) {
//             throw new Exception("Impossible de sauvegarder les modifications");
//         }
        
//         logError("Club modifié avec succès", ['id' => $data['id']]);
        
//         echo json_encode([
//             'success' => true,
//             'message' => 'Club modifié avec succès',
//             'club' => $data
//         ]);
        
//     } catch (Exception $e) {
//         logError("Erreur modification club", ['error' => $e->getMessage()]);
//         http_response_code(500);
//         echo json_encode([
//             'success' => false,
//             'message' => $e->getMessage()
//         ]);
//     }
// }

// // =============================================
// // SUPPRIMER UN CLUB
// // =============================================

// function supprimerClub() {
//     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//         http_response_code(405);
//         echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
//         return;
//     }

//     try {
//         $json = file_get_contents('php://input');
//         $data = json_decode($json, true);
        
//         if (!$data || empty($data['id'])) {
//             throw new Exception("ID du club manquant");
//         }
        
//         $xmlFile = CLUBS_XML;
//         if (!file_exists($xmlFile)) {
//             throw new Exception("Fichier clubs.xml introuvable");
//         }
        
//         $xml = simplexml_load_file($xmlFile);
//         if (!$xml) {
//             throw new Exception("Impossible de charger clubs.xml");
//         }
        
//         // Chercher et supprimer le club
//         $trouve = false;
//         $index = 0;
//         foreach ($xml->club as $club) {
//             if ((string)$club['id'] === $data['id']) {
//                 unset($xml->club[$index]);
//                 $trouve = true;
//                 break;
//             }
//             $index++;
//         }
        
//         if (!$trouve) {
//             throw new Exception("Club avec ID {$data['id']} introuvable");
//         }
        
//         // Sauvegarder
//         $dom = new DOMDocument('1.0', 'UTF-8');
//         $dom->preserveWhiteSpace = false;
//         $dom->formatOutput = true;
//         $dom->loadXML($xml->asXML());
        
//         if (!$dom->save($xmlFile)) {
//             throw new Exception("Impossible de sauvegarder les modifications");
//         }
        
//         logError("Club supprimé avec succès", ['id' => $data['id']]);
        
//         echo json_encode([
//             'success' => true,
//             'message' => 'Club supprimé avec succès',
//             'id' => $data['id']
//         ]);
        
//     } catch (Exception $e) {
//         logError("Erreur suppression club", ['error' => $e->getMessage()]);
//         http_response_code(500);
//         echo json_encode([
//             'success' => false,
//             'message' => $e->getMessage()
//         ]);
//     }
// }

// // =============================================
// // LISTER LES CLUBS
// // =============================================

// function listerClubs() {
//     try {
//         $xmlFile = CLUBS_XML;
//         if (!file_exists($xmlFile)) {
//             echo json_encode([
//                 'success' => true,
//                 'clubs' => []
//             ]);
//             return;
//         }
        
//         $xml = simplexml_load_file($xmlFile);
//         if (!$xml) {
//             throw new Exception("Impossible de charger clubs.xml");
//         }
        
//         $clubs = [];
//         foreach ($xml->club as $club) {
//             $clubs[] = [
//                 'id' => (string)$club['id'],
//                 'nomFr' => (string)$club->nom['fr'],
//                 'nomEn' => (string)$club->nom['en'],
//                 'descFr' => (string)$club->description['fr'],
//                 'descEn' => (string)$club->description['en']
//             ];
//         }
        
//         echo json_encode([
//             'success' => true,
//             'clubs' => $clubs
//         ]);
        
//     } catch (Exception $e) {
//         http_response_code(500);
//         echo json_encode([
//             'success' => false,
//             'message' => $e->getMessage()
//         ]);
//     }
// }
?>
<?php
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic - Club Informatique</title>
    <style>
        body { font-family: Arial; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .success { color: green; background: #e8f5e9; padding: 10px; margin: 5px 0; border-left: 4px solid green; }
        .error { color: red; background: #ffebee; padding: 10px; margin: 5px 0; border-left: 4px solid red; }
        .warning { color: orange; background: #fff3e0; padding: 10px; margin: 5px 0; border-left: 4px solid orange; }
        .info { color: blue; background: #e3f2fd; padding: 10px; margin: 5px 0; border-left: 4px solid blue; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h2 { color: #667eea; margin-top: 30px; }
    </style>
</head>
<body>
    <h1>🔍 Diagnostic du système</h1>";

// 1. Vérifier PHP
echo "<h2>1️⃣ Version PHP</h2>";
echo "<div class='info'>PHP Version: " . phpversion() . "</div>";

// 2. Vérifier config.php
echo "<h2>2️⃣ Fichier config.php</h2>";
if (file_exists(__DIR__ . '/config.php')) {
    echo "<div class='success'>✅ config.php existe</div>";
    require_once __DIR__ . '/config.php';
    echo "<div class='info'>XML_DIR défini: " . XML_DIR . "</div>";
} else {
    echo "<div class='error'>❌ config.php introuvable</div>";
    exit;
}

// 3. Vérifier le dossier XML
echo "<h2>3️⃣ Dossier XML</h2>";
if (file_exists(XML_DIR)) {
    echo "<div class='success'>✅ Dossier XML existe</div>";
    echo "<div class='info'>Chemin: " . XML_DIR . "</div>";
    
    if (is_writable(XML_DIR)) {
        echo "<div class='success'>✅ Dossier accessible en écriture</div>";
    } else {
        echo "<div class='error'>❌ Dossier NON accessible en écriture</div>";
        echo "<div class='warning'>Exécutez: chmod 777 " . XML_DIR . "</div>";
    }
    
    $perms = substr(sprintf('%o', fileperms(XML_DIR)), -4);
    echo "<div class='info'>Permissions: " . $perms . "</div>";
} else {
    echo "<div class='warning'>⚠️ Dossier XML n'existe pas - tentative de création...</div>";
    try {
        ensureXmlDirExists();
        echo "<div class='success'>✅ Dossier XML créé avec succès</div>";
    } catch (Exception $e) {
        echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
    }
}

// 4. Vérifier les fichiers XML
echo "<h2>4️⃣ Fichiers XML</h2>";

$xmlFiles = [
    'membres.xml' => MEMBRES_XML,
    'activites.xml' => ACTIVITES_XML,
    'participations.xml' => PARTICIPATIONS_XML,
    'users.xml' => USERS_XML
];

foreach ($xmlFiles as $name => $path) {
    echo "<h3>$name</h3>";
    if (file_exists($path)) {
        echo "<div class='success'>✅ Fichier existe</div>";
        
        if (is_readable($path)) {
            echo "<div class='success'>✅ Lisible</div>";
        } else {
            echo "<div class='error'>❌ NON lisible</div>";
        }
        
        if (is_writable($path)) {
            echo "<div class='success'>✅ Accessible en écriture</div>";
        } else {
            echo "<div class='error'>❌ NON accessible en écriture</div>";
        }
        
        $size = filesize($path);
        echo "<div class='info'>Taille: " . $size . " octets</div>";
        
        // Essayer de charger le XML
        $xml = @simplexml_load_file($path);
        if ($xml !== false) {
            echo "<div class='success'>✅ XML valide</div>";
            
            // Afficher le contenu
            if ($name === 'membres.xml') {
                $count = count($xml->membre);
                echo "<div class='info'>Nombre de membres: $count</div>";
                
                if ($count > 0) {
                    echo "<pre>" . htmlspecialchars($xml->asXML()) . "</pre>";
                }
            }
        } else {
            echo "<div class='error'>❌ XML invalide ou corrompu</div>";
        }
        
    } else {
        echo "<div class='warning'>⚠️ Fichier n'existe pas</div>";
    }
}

// 5. Test de création de membre
echo "<h2>5️⃣ Test de création de membre</h2>";

try {
    ensureXmlDirExists();
    
    $testData = [
        'nom' => 'Test Diagnostic',
        'email' => 'test@diagnostic.com',
        'telephone' => '0600000000'
    ];
    
    $xmlFile = MEMBRES_XML;
    
    if (file_exists($xmlFile)) {
        $xml = simplexml_load_file($xmlFile);
    } else {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><membres></membres>');
    }
    
    // Vérifier si le membre de test existe déjà
    $exists = false;
    foreach ($xml->membre as $m) {
        if ((string)$m->email === $testData['email']) {
            $exists = true;
            echo "<div class='info'>ℹ️ Membre de test existe déjà</div>";
            break;
        }
    }
    
    if (!$exists) {
        $membre = $xml->addChild('membre');
        $membre->addAttribute('id', 'TEST-' . time());
        $membre->addChild('nom', $testData['nom']);
        $membre->addChild('email', $testData['email']);
        $membre->addChild('telephone', $testData['telephone']);
        $membre->addChild('dateAdhesion', date('Y-m-d H:i:s'));
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        
        if ($dom->save($xmlFile)) {
            echo "<div class='success'>✅ Membre de test ajouté avec succès!</div>";
            echo "<pre>" . htmlspecialchars($dom->saveXML()) . "</pre>";
        } else {
            echo "<div class='error'>❌ Impossible de sauvegarder le membre de test</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur lors du test: " . $e->getMessage() . "</div>";
    echo "<div class='error'>Trace: <pre>" . $e->getTraceAsString() . "</pre></div>";
}

// 6. Logs
echo "<h2>6️⃣ Logs d'erreurs</h2>";

$logFile = __DIR__ . '/app.log';
if (file_exists($logFile)) {
    echo "<div class='info'>📄 Contenu de app.log:</div>";
    echo "<pre>" . htmlspecialchars(file_get_contents($logFile)) . "</pre>";
} else {
    echo "<div class='info'>Aucun fichier de log trouvé</div>";
}

$phpErrorLog = __DIR__ . '/php-errors.log';
if (file_exists($phpErrorLog)) {
    echo "<div class='info'>📄 Contenu de php-errors.log:</div>";
    echo "<pre>" . htmlspecialchars(file_get_contents($phpErrorLog)) . "</pre>";
} else {
    echo "<div class='info'>Aucune erreur PHP loggée</div>";
}

echo "
    <hr>
    <h2>✅ Actions recommandées</h2>
    <ol>
        <li>Vérifier que tous les fichiers XML existent et sont accessibles en écriture</li>
        <li>Vérifier les permissions: <code>chmod -R 777 xml/</code></li>
        <li>Tester l'API directement: <a href='membres.php?action=lister'>membres.php?action=lister</a></li>
        <li>Vérifier la console JavaScript pour les erreurs côté client</li>
    </ol>
</body>
</html>";
?>
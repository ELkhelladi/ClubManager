<?php
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Réparation - Club Informatique</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #e8f5e9; padding: 10px; margin: 10px 0; border-left: 4px solid green; }
        .error { color: red; background: #ffebee; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
        .info { color: blue; background: #e3f2fd; padding: 10px; margin: 10px 0; border-left: 4px solid blue; }
        h1 { color: #667eea; }
        button { background: #667eea; color: white; border: none; padding: 15px 30px; border-radius: 8px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        button:hover { background: #5568d3; }
    </style>
</head>
<body>
    <h1>🔧 Réparation des fichiers XML</h1>";

// Fonction pour créer un fichier XML vide et valide
function creerXMLVide($fichier, $racine) {
    try {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $root = $xml->createElement($racine);
        $xml->appendChild($root);
        
        if ($xml->save($fichier)) {
            @chmod($fichier, 0666);
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Vérifier et réparer membres.xml
echo "<h2>📝 membres.xml</h2>";
if (!file_exists(MEMBRES_XML) || filesize(MEMBRES_XML) === 0) {
    echo "<div class='error'>❌ Fichier vide ou inexistant</div>";
    
    if (creerXMLVide(MEMBRES_XML, 'membres')) {
        echo "<div class='success'>✅ Fichier recréé avec succès!</div>";
        echo "<div class='info'>Contenu:<pre>" . htmlspecialchars(file_get_contents(MEMBRES_XML)) . "</pre></div>";
    } else {
        echo "<div class='error'>❌ Impossible de recréer le fichier</div>";
    }
} else {
    $xml = @simplexml_load_file(MEMBRES_XML);
    if ($xml === false) {
        echo "<div class='error'>❌ Fichier corrompu - réparation...</div>";
        
        // Sauvegarder le contenu corrompu
        @rename(MEMBRES_XML, MEMBRES_XML . '.backup');
        
        if (creerXMLVide(MEMBRES_XML, 'membres')) {
            echo "<div class='success'>✅ Fichier réparé! (ancien fichier sauvegardé en .backup)</div>";
        } else {
            echo "<div class='error'>❌ Impossible de réparer</div>";
        }
    } else {
        echo "<div class='success'>✅ Fichier valide</div>";
        $count = count($xml->membre);
        echo "<div class='info'>Nombre de membres: $count</div>";
    }
}

// Vérifier activites.xml
echo "<h2>📅 activites.xml</h2>";
if (!file_exists(ACTIVITES_XML) || filesize(ACTIVITES_XML) === 0) {
    if (creerXMLVide(ACTIVITES_XML, 'activites')) {
        echo "<div class='success'>✅ Fichier créé</div>";
    } else {
        echo "<div class='error'>❌ Erreur création</div>";
    }
} else {
    $xml = @simplexml_load_file(ACTIVITES_XML);
    if ($xml === false) {
        @rename(ACTIVITES_XML, ACTIVITES_XML . '.backup');
        creerXMLVide(ACTIVITES_XML, 'activites');
        echo "<div class='success'>✅ Fichier réparé</div>";
    } else {
        echo "<div class='success'>✅ Fichier valide</div>";
        $count = count($xml->activite);
        echo "<div class='info'>Nombre d'activités: $count</div>";
    }
}

// Vérifier participations.xml
echo "<h2>👥 participations.xml</h2>";
if (!file_exists(PARTICIPATIONS_XML) || filesize(PARTICIPATIONS_XML) === 0) {
    if (creerXMLVide(PARTICIPATIONS_XML, 'participations')) {
        echo "<div class='success'>✅ Fichier créé</div>";
    } else {
        echo "<div class='error'>❌ Erreur création</div>";
    }
} else {
    $xml = @simplexml_load_file(PARTICIPATIONS_XML);
    if ($xml === false) {
        @rename(PARTICIPATIONS_XML, PARTICIPATIONS_XML . '.backup');
        creerXMLVide(PARTICIPATIONS_XML, 'participations');
        echo "<div class='success'>✅ Fichier réparé</div>";
    } else {
        echo "<div class='success'>✅ Fichier valide</div>";
        $count = count($xml->participation);
        echo "<div class='info'>Nombre de participations: $count</div>";
    }
}

// Test final
echo "<h2>✅ Test final</h2>";

try {
    // Test de lecture
    $xmlMembres = @simplexml_load_file(MEMBRES_XML);
    $xmlActivites = @simplexml_load_file(ACTIVITES_XML);
    $xmlParticipations = @simplexml_load_file(PARTICIPATIONS_XML);
    
    if ($xmlMembres !== false && $xmlActivites !== false && $xmlParticipations !== false) {
        echo "<div class='success'>
            <h3>🎉 TOUT EST PRÊT !</h3>
            <p>Tous les fichiers XML sont valides et accessibles.</p>
            <p>Vous pouvez maintenant utiliser l'application.</p>
        </div>";
        
        echo "<div style='margin-top: 30px; text-align: center;'>
            <a href='diagnostic.php'><button>🔍 Voir le diagnostic</button></a>
            <a href='membres.php?action=lister'><button>📝 Tester l'API Membres</button></a>
            <a href='../html/superviseur.html'><button>👨‍💼 Interface Superviseur</button></a>
        </div>";
    } else {
        echo "<div class='error'>❌ Certains fichiers sont encore corrompus</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erreur: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>
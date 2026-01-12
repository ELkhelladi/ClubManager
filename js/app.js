
// Fonction pour charger un fichier XML
function loadXML(path) {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", path, false);
    try {
        xhr.send();
        if (xhr.status === 200) {
            return xhr.responseXML;
        }
    } catch (e) {
        console.error("Erreur chargement XML:", path, e);
    }
    return null;
}

console.log('✅ app.js chargé');
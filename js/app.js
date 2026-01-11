let currentLang = 'fr';
let langTexts = {};

function loadXML(path) {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", path, false);
  xhr.send();
  return xhr.responseXML;
}

function chargerLangue(lang) {
  currentLang = lang;
  const xml = loadXML(`xml/lang_${lang}.xml`);
  if (!xml) { alert("Fichier langue introuvable"); return; }

  const langNode = xml.getElementsByTagName("lang")[0];
  langTexts = {};
  for (let i=0;i<langNode.children.length;i++){
    langTexts[langNode.children[i].tagName] = langNode.children[i].textContent;
  }

  document.getElementById("titrePage").innerText = langTexts['titre'];
  document.getElementById("labelLangue").innerText = langTexts['choixLangue'];

  afficherClubs();
}

function changerLangue(lang) {
  chargerLangue(lang);
}

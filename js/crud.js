function afficherClubs() {
  const xml = loadXML("xml/clubs.xml");
  if (!xml) { document.getElementById("clubsContent").innerText="Erreur clubs XML"; return; }

  const clubs = xml.getElementsByTagName("club");
  let html = `<h2>${langTexts['listeClubs']}</h2><ul>`;

  for (let club of clubs) {
    const clubId = club.getAttribute("id");
    const nom = club.getElementsByTagName("nom")[0].getAttribute(currentLang);
    const description = club.getElementsByTagName("description")[0].getAttribute(currentLang);

    html += `
      <li>
        <b>${nom}</b> - ${description}
        <button onclick="afficherLogin(${clubId})">${langTexts['login']}</button>
        <button onclick="afficherFormulaire(${clubId})">${langTexts['demande']}</button>
      </li>
    `;
  }
  html += "</ul>";
  document.getElementById("clubsContent").innerHTML = html;
  document.getElementById("formulaireClub").innerHTML = "";
}

function afficherFormulaire(clubId) {
  const xml = loadXML("xml/clubs.xml");
  const club = xml.querySelector(`club[id="${clubId}"]`);
  const nom = club.getElementsByTagName("nom")[0].getAttribute(currentLang);

  let formDiv = document.getElementById('formulaireClub');
  formDiv.innerHTML = `
    <h3>${langTexts['formulaireTitre']} ${nom}</h3>
    <input id="nomEtudiant" placeholder="${langTexts['nomEtudiant']}">
    <button onclick="envoyerDemande(${clubId})">${langTexts['envoyer']}</button>
  `;
}

function envoyerDemande(clubId) {
  const nom = document.getElementById("nomEtudiant").value;
  if (!nom) { alert(langTexts['erreurNom']); return; }
  alert(`${langTexts['demandeEnvoyee']} Club ${clubId} par ${nom}`);
}

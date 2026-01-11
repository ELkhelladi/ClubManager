function afficherDemandes(role) {
  if (!hasPermission(role, "TRAITER_DEMANDE")) {
    alert("Accès refusé");
    return;
  }

  const xml = loadXML("xml/demandes.xml");
  const demandes = xml.getElementsByTagName("demande");

  let html = "<h2>Demandes en attente</h2>";

  for (let d of demandes) {
    const etudiant = d.getElementsByTagName("nomEtudiant")[0].textContent;
    const clubId = d.getElementsByTagName("clubId")[0].textContent;
    const statut = d.getElementsByTagName("statut")[0].textContent;

    html += `
      <p>
        ${etudiant} - Club ID: ${clubId} - Statut: ${statut}
        <button onclick="accepter('${etudiant}', ${clubId})">Accepter</button>
        <button onclick="rejeter('${etudiant}', ${clubId})">Rejeter</button>
      </p>`;
  }

  document.getElementById("content").innerHTML = html;
}

function accepter(etudiant, clubId) {
  alert(`${etudiant} accepté au club ${clubId} → rôle devient Membre`);
}

function rejeter(etudiant, clubId) {
  alert(`${etudiant} rejeté du club ${clubId}`);
}

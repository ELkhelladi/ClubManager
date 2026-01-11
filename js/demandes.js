function afficherDemandes(role) {
  if (!hasPermission(role, "TRAITER_DEMANDE")) {
    alert("Accès refusé");
    return;
  }

  let html = "<h2>Demandes en attente</h2>";
  let compteur = 0;

  // 1️⃣ Demandes depuis XML (existantes)
  const xml = loadXML("xml/demandes.xml");
  const demandesXML = xml ? xml.getElementsByTagName("demande") : [];

  for (let d of demandesXML) {
    if (d.getElementsByTagName("statut")[0].textContent !== "en_attente") continue;
    
    const nomEtudiant = d.getElementsByTagName("nomEtudiant")[0].textContent;
    const clubId = d.getElementsByTagName("clubId")[0].textContent;
    const demandeId = d.getAttribute("id");

    html += `
      <div class="demande-card">
        <p><strong>${nomEtudiant}</strong> - Club ${clubId}</p>
        <button onclick="accepterDemandeXML('${demandeId}', '${nomEtudiant}', '${clubId}')">Accepter</button>
        <button onclick="rejeterDemandeXML('${demandeId}', '${nomEtudiant}', '${clubId}')">Rejeter</button>
      </div>
    `;
    compteur++;
  }

  // 2️⃣ Demandes depuis localStorage (nouvelles)
  const demandesLocal = JSON.parse(localStorage.getItem("demandes")) || [];

  for (let d of demandesLocal) {
    if (d.statut !== "en_attente") continue;

    const dateCreation = new Date(d.dateCreation).toLocaleString('fr-FR');

    html += `
      <div class="demande-card" style="background: #f0f8ff; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff;">
        <p><strong>${d.nomEtudiant}</strong></p>
        <p>Email: ${d.email || 'Non fourni'}</p>
        <p>Club ID: ${d.clubId}</p>
        <p>Motivation: ${d.motivation || 'Aucune'}</p>
        <p style="font-size: 0.85em; color: #666;">Date: ${dateCreation}</p>
        <button onclick="traiterDemandeLocal('${d.id}', 'accepter')">Accepter</button>
        <button onclick="traiterDemandeLocal('${d.id}', 'rejeter')">Rejeter</button>
      </div>
    `;
    compteur++;
  }

  if (compteur === 0) {
    html += "<p>Aucune demande en attente</p>";
  }

  // Bouton pour exporter les demandes
  html += `
    <hr>
    <button onclick="exporterDemandesVersXML()">📥 Exporter les demandes en XML</button>
    <button onclick="afficherStatistiques()">📊 Voir les statistiques</button>
  `;

  document.getElementById("content").innerHTML = html;
}

function traiterDemandeLocal(demandeId, action) {
  let demandes = JSON.parse(localStorage.getItem("demandes")) || [];
  
  const index = demandes.findIndex(d => d.id === demandeId);
  if (index === -1) {
    alert("Demande introuvable");
    return;
  }

  const demande = demandes[index];
  
  if (action === 'accepter') {
    demandes[index].statut = 'acceptee';
    demandes[index].dateTraitement = new Date().toISOString();
    alert(`${demande.nomEtudiant} accepté(e) au club ${demande.clubId}\n✅ Rôle devient: Membre`);
    
    // Optionnel: ajouter l'utilisateur aux membres
    ajouterMembreLocal(demande);
  } else if (action === 'rejeter') {
    demandes[index].statut = 'rejetee';
    demandes[index].dateTraitement = new Date().toISOString();
    alert(`❌ ${demande.nomEtudiant} rejeté(e) du club ${demande.clubId}`);
  }

  localStorage.setItem("demandes", JSON.stringify(demandes));
  
  // Rafraîchir l'affichage
  afficherDemandes('Superviseur');
}

function ajouterMembreLocal(demande) {
  let membres = JSON.parse(localStorage.getItem("membres")) || [];
  
  membres.push({
    id: "M" + Date.now(),
    nom: demande.nomEtudiant,
    email: demande.email,
    clubId: demande.clubId,
    dateAdhesion: new Date().toISOString(),
    role: "membre"
  });
  
  localStorage.setItem("membres", JSON.stringify(membres));
}

function accepterDemandeXML(demandeId, etudiant, clubId) {
  alert(`${etudiant} accepté au club ${clubId} → rôle devient Membre`);
  // Note: impossible de modifier XML côté client
  console.warn("Les demandes XML ne peuvent pas être modifiées côté client");
}

function rejeterDemandeXML(demandeId, etudiant, clubId) {
  alert(`${etudiant} rejeté du club ${clubId}`);
  console.warn("Les demandes XML ne peuvent pas être modifiées côté client");
}

function afficherStatistiques() {
  const demandes = JSON.parse(localStorage.getItem("demandes")) || [];
  
  const stats = {
    total: demandes.length,
    enAttente: demandes.filter(d => d.statut === 'en_attente').length,
    acceptees: demandes.filter(d => d.statut === 'acceptee').length,
    rejetees: demandes.filter(d => d.statut === 'rejetee').length
  };

  const statsHTML = `
    <div style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
      <h3>📊 Statistiques des demandes</h3>
      <ul style="list-style: none; padding: 0;">
        <li>📝 Total: <strong>${stats.total}</strong></li>
        <li>⏳ En attente: <strong>${stats.enAttente}</strong></li>
        <li>✅ Acceptées: <strong>${stats.acceptees}</strong></li>
        <li>❌ Rejetées: <strong>${stats.rejetees}</strong></li>
      </ul>
      <button onclick="afficherDemandes('Superviseur')">Retour aux demandes</button>
    </div>
  `;

  document.getElementById("content").innerHTML = statsHTML;
}
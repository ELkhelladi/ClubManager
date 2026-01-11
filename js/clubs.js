// =============================================
// CLUBS.JS - Gestion complète des clubs
// =============================================

let clubsData = [];

// =============================================
// CHARGER TOUS LES CLUBS
// =============================================

function chargerClubs() {
  console.log("🔄 Chargement des clubs...");
  
  try {
    const xml = loadXML("../xml/clubs.xml");
    if (!xml) {
      document.getElementById("clubsList").innerHTML = `
        <div style="text-align: center; padding: 40px; color: #999;">
          <p style="font-size: 48px;">❌</p>
          <p>Erreur lors du chargement de clubs.xml</p>
        </div>
      `;
      return;
    }

    const clubs = xml.getElementsByTagName("club");
    clubsData = [];
    
    for (let club of clubs) {
      const clubId = club.getAttribute("id");
      const nomNode = club.getElementsByTagName("nom")[0];
      const descNode = club.getElementsByTagName("description")[0];
      
      clubsData.push({
        id: clubId,
        nomFr: nomNode.getAttribute("fr"),
        nomEn: nomNode.getAttribute("en"),
        descFr: descNode.getAttribute("fr"),
        descEn: descNode.getAttribute("en")
      });
    }

    afficherClubs();
    console.log(`✅ ${clubsData.length} clubs chargés`);
    
  } catch (error) {
    console.error("❌ Erreur:", error);
    document.getElementById("clubsList").innerHTML = `
      <div style="text-align: center; padding: 40px; color: #f44336;">
        <p>Erreur: ${error.message}</p>
      </div>
    `;
  }
}

// =============================================
// AFFICHER LES CLUBS
// =============================================

function afficherClubs() {
  const container = document.getElementById("clubsList");
  
  if (clubsData.length === 0) {
    container.innerHTML = `
      <div style="text-align: center; padding: 40px; color: #999;">
        <p style="font-size: 48px;">📭</p>
        <p style="font-size: 18px;">Aucun club enregistré</p>
        <button class="btn btn-success" onclick="ouvrirModalAjouter()" style="margin-top: 20px;">
          ➕ Ajouter le premier club
        </button>
      </div>
    `;
    return;
  }

  let html = `<h2 style="margin-bottom: 20px; color: #333;">📋 Liste des clubs (${clubsData.length})</h2>`;
  
  clubsData.forEach(club => {
    html += `
      <div class="club-card">
        <div class="club-header">
          <span class="club-name">${club.nomFr}</span>
          <span class="club-id">ID: ${club.id}</span>
        </div>
        <div class="club-description">
          <strong>🇫🇷 FR:</strong> ${club.descFr}<br>
          <strong>🇬🇧 EN:</strong> ${club.descEn}
        </div>
        <div style="color: #999; font-size: 12px; margin-top: 10px;">
          <strong>EN:</strong> ${club.nomEn}
        </div>
        <div class="club-actions">
          <button class="btn btn-warning" onclick='ouvrirModalModifier(${JSON.stringify(club).replace(/'/g, "&apos;")})'>
            ✏️ Modifier
          </button>
          <button class="btn btn-danger" onclick="confirmerSuppression('${club.id}', '${club.nomFr}')">
            🗑️ Supprimer
          </button>
        </div>
      </div>
    `;
  });

  container.innerHTML = html;
}

// =============================================
// AJOUTER UN CLUB
// =============================================

async function ajouterClub(event) {
  event.preventDefault();

  // Vérifier les permissions
  if (!hasPermission('Responsable', 'CRUD_CLUB')) {
    alert("❌ Accès refusé");
    return;
  }

  // Récupérer les données du formulaire
  const clubData = {
    id: "C" + Date.now(),
    nomFr: document.getElementById('nomFr').value.trim(),
    nomEn: document.getElementById('nomEn').value.trim(),
    descFr: document.getElementById('descFr').value.trim(),
    descEn: document.getElementById('descEn').value.trim()
  };

  console.log("📤 Envoi du nouveau club:", clubData);

  try {
    const response = await fetch('../backend/clubs.php?action=ajouter', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(clubData)
    });

    const result = await response.json();
    console.log("📦 Résultat:", result);

    if (result.success) {
      afficherNotification("✅ Club ajouté avec succès!", "success");
      fermerModal('modalAjouter');
      chargerClubs();
    } else {
      throw new Error(result.message || "Erreur lors de l'ajout");
    }

  } catch (error) {
    console.error("❌ Erreur:", error);
    alert("❌ Erreur: " + error.message);
  }
}

// =============================================
// MODIFIER UN CLUB
// =============================================

function ouvrirModalModifier(club) {
  document.getElementById('modifClubId').value = club.id;
  document.getElementById('modifNomFr').value = club.nomFr;
  document.getElementById('modifNomEn').value = club.nomEn;
  document.getElementById('modifDescFr').value = club.descFr;
  document.getElementById('modifDescEn').value = club.descEn;
  
  ouvrirModal('modalModifier');
}

async function modifierClub(event) {
  event.preventDefault();

  if (!hasPermission('Responsable', 'CRUD_CLUB')) {
    alert("❌ Accès refusé");
    return;
  }

  const clubData = {
    id: document.getElementById('modifClubId').value,
    nomFr: document.getElementById('modifNomFr').value.trim(),
    nomEn: document.getElementById('modifNomEn').value.trim(),
    descFr: document.getElementById('modifDescFr').value.trim(),
    descEn: document.getElementById('modifDescEn').value.trim()
  };

  console.log("📤 Modification du club:", clubData);

  try {
    const response = await fetch('../backend/clubs.php?action=modifier', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(clubData)
    });

    const result = await response.json();

    if (result.success) {
      afficherNotification("✅ Club modifié avec succès!", "success");
      fermerModal('modalModifier');
      chargerClubs();
    } else {
      throw new Error(result.message);
    }

  } catch (error) {
    console.error("❌ Erreur:", error);
    alert("❌ Erreur: " + error.message);
  }
}

// =============================================
// SUPPRIMER UN CLUB
// =============================================

function confirmerSuppression(clubId, clubNom) {
  if (!hasPermission('Responsable', 'CRUD_CLUB')) {
    alert("❌ Accès refusé");
    return;
  }

  const confirmation = confirm(
    `⚠️ ATTENTION\n\n` +
    `Êtes-vous sûr de vouloir supprimer le club:\n` +
    `"${clubNom}" (ID: ${clubId}) ?\n\n` +
    `Cette action est IRRÉVERSIBLE!`
  );

  if (confirmation) {
    supprimerClub(clubId);
  }
}

async function supprimerClub(clubId) {
  console.log("🗑️ Suppression du club:", clubId);

  try {
    const response = await fetch('../backend/clubs.php?action=supprimer', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id: clubId })
    });

    const result = await response.json();

    if (result.success) {
      afficherNotification("✅ Club supprimé avec succès!", "success");
      chargerClubs();
    } else {
      throw new Error(result.message);
    }

  } catch (error) {
    console.error("❌ Erreur:", error);
    alert("❌ Erreur: " + error.message);
  }
}

// =============================================
// EXPORTER EN XML
// =============================================

function exporterClubsXML() {
  if (clubsData.length === 0) {
    alert("Aucun club à exporter");
    return;
  }

  let xmlString = '<?xml version="1.0" encoding="UTF-8"?>\n<clubs>\n';
  
  clubsData.forEach(club => {
    xmlString += `  <club id="${club.id}">\n`;
    xmlString += `    <nom fr="${escapeXMLAttr(club.nomFr)}" en="${escapeXMLAttr(club.nomEn)}" />\n`;
    xmlString += `    <description fr="${escapeXMLAttr(club.descFr)}" en="${escapeXMLAttr(club.descEn)}" />\n`;
    xmlString += `  </club>\n`;
  });
  
  xmlString += '</clubs>';
  
  // Télécharger
  const blob = new Blob([xmlString], { type: 'text/xml' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `clubs_export_${new Date().toISOString().split('T')[0]}.xml`;
  a.click();
  URL.revokeObjectURL(url);
  
  afficherNotification("📥 Fichier XML téléchargé!", "success");
}

function escapeXMLAttr(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

// =============================================
// NOTIFICATION
// =============================================

function afficherNotification(message, type = 'success') {
  const colors = {
    success: '#4caf50',
    error: '#f44336',
    warning: '#ff9800',
    info: '#2196f3'
  };
  
  const notif = document.createElement('div');
  notif.textContent = message;
  notif.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    background: ${colors[type]};
    color: white;
    border-radius: 5px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 10000;
    font-weight: bold;
    animation: slideIn 0.3s ease;
  `;
  
  document.body.appendChild(notif);
  
  setTimeout(() => {
    notif.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => notif.remove(), 300);
  }, 3000);
}

// Ajouter les animations CSS si pas déjà présentes
if (!document.getElementById('notif-styles')) {
  const style = document.createElement('style');
  style.id = 'notif-styles';
  style.textContent = `
    @keyframes slideIn {
      from { transform: translateX(400px); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
      from { transform: translateX(0); opacity: 1; }
      to { transform: translateX(400px); opacity: 0; }
    }
  `;
  document.head.appendChild(style);
}
function ajouterClub(role) {
  if (!hasPermission(role, "CRUD_CLUB")) { alert("Accès refusé"); return; }
  const nom = prompt("Nom du club");
  if (!nom) return;
 
  const xml = loadXML("xml/clubs.xml");
  const club = xml.createElement("club");
  club.setAttribute("id", Date.now());

  const nomNode = xml.createElement("nom");
  nomNode.setAttribute("fr", nom);
  nomNode.setAttribute("en", nom);

  club.appendChild(nomNode);
  xml.documentElement.appendChild(club);

  alert("Club ajouté (simulation)");
}

function modifierClub(role) {
  if (!hasPermission(role, "CRUD_CLUB")) { alert("Accès refusé"); return; }
  alert("Club modifié");
}

function supprimerClub(role) {
  if (!hasPermission(role, "CRUD_CLUB")) { alert("Accès refusé"); return; }
  alert("Club supprimé");
}

function ajouterClub(role) {
  if (!hasPermission(role, "CRUD_CLUB")) { alert("Accès refusé"); return; }
  alert("Club ajouté (simulation XML)");
}

function modifierClub(role) {
  if (!hasPermission(role, "CRUD_CLUB")) { alert("Accès refusé"); return; }
  alert("Club modifié");
}

function supprimerClub(role) {
  if (!hasPermission(role, "CRUD_CLUB")) { alert("Accès refusé"); return; }
  alert("Club supprimé");
}

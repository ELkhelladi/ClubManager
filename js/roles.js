const Roles = {
  VISITEUR: "Visiteur",
  SUPERVISEUR: "Superviseur",
  RESPONSABLE: "Responsable",
  MEMBRE: "Membre"
};

function hasPermission(role, action) {
  if (role === Roles.RESPONSABLE) return true;

  if (role === Roles.SUPERVISEUR && action === "TRAITER_DEMANDE")
    return true;

  if (role === Roles.VISITEUR && action === "CREER_DEMANDE")
    return true;

  return false;
}

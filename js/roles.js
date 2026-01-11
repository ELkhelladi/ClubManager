// =============================================
// GESTION DES RÔLES
// =============================================

const Roles = {
    MEMBRE: "membre",
    SUPERVISEUR: "superviseur"
};

const Permissions = {
    // Permissions Superviseur
    GERER_MEMBRES: "gerer_membres",
    GERER_ACTIVITES: "gerer_activites",
    VOIR_PARTICIPANTS: "voir_participants",
    
    // Permissions Membre
    VOIR_ACTIVITES: "voir_activites",
    REJOINDRE_ACTIVITE: "rejoindre_activite",
    ANNULER_PARTICIPATION: "annuler_participation"
};

// Vérifier si un rôle a une permission
function hasPermission(role, permission) {
    const rolePermissions = {
        [Roles.SUPERVISEUR]: [
            Permissions.GERER_MEMBRES,
            Permissions.GERER_ACTIVITES,
            Permissions.VOIR_PARTICIPANTS,
            Permissions.VOIR_ACTIVITES
        ],
        [Roles.MEMBRE]: [
            Permissions.VOIR_ACTIVITES,
            Permissions.REJOINDRE_ACTIVITE,
            Permissions.ANNULER_PARTICIPATION
        ]
    };

    return rolePermissions[role]?.includes(permission) || false;
}

// Obtenir le rôle de l'utilisateur connecté
function getCurrentUserRole() {
    return sessionStorage.getItem('role');
}

// Obtenir le nom de l'utilisateur connecté
function getCurrentUsername() {
    return sessionStorage.getItem('username');
}

// Vérifier si l'utilisateur est connecté
function isAuthenticated() {
    return !!sessionStorage.getItem('username');
}

// Vérifier et rediriger si non autorisé
function requireRole(allowedRoles) {
    if (!isAuthenticated()) {
        window.location.href = '../index.html';
        return false;
    }

    const currentRole = getCurrentUserRole();
    if (!allowedRoles.includes(currentRole)) {
        alert('Accès non autorisé');
        window.location.href = '../index.html';
        return false;
    }

    return true;
}

// Déconnexion
function logout() {
    sessionStorage.clear();
    window.location.href = '../index.html';
}











// const Roles = {
//   VISITEUR: "Visiteur",
//   SUPERVISEUR: "Superviseur",
//   RESPONSABLE: "Responsable",
//   MEMBRE: "Membre"
// };

// function hasPermission(role, action) {
//   if (role === Roles.RESPONSABLE) return true;

//   if (role === Roles.SUPERVISEUR && action === "TRAITER_DEMANDE")
//     return true;

//   if (role === Roles.VISITEUR && action === "CREER_DEMANDE")
//     return true;

//   return false;
// }

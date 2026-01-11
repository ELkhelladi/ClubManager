// function afficherLogin(clubId) {
//   const username = prompt(langTexts['promptUsername']).trim();
//   const password = prompt(langTexts['promptPassword']).trim();

//   const usersXML = loadXML("xml/users.xml");
//   if (!usersXML) { alert("Erreur chargement users.xml"); return; }

//   const users = usersXML.getElementsByTagName("user");
//   let connected = false;

//   for (let i = 0; i < users.length; i++) {
//     const user = users[i];
//     const xmlUsername = user.getAttribute("username").trim();
//     const xmlPassword = user.getAttribute("password").trim();
//     const role = user.getAttribute("role").trim();

//     if(xmlUsername === username && xmlPassword === password) {
//       connected = true;
//       alert(`${langTexts['loginSuccess']} (${role})`);

//       // redirection selon rôle
//       if(role === "membre") {
//         window.location.href = "html/membre.html";
//       } else if(role === "superviseur") {
//         window.location.href = "html/superviseur.html";
//       } else if(role === "admin") {
//         window.location.href = "html/responsable.html";
//       }
//       break;
//     }
//   }

//   if (!connected) alert(langTexts['loginFailed']);
// }

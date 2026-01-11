// function afficherClubs() {
//   const xml = loadXML("xml/clubs.xml");
//   if (!xml) { 
//     document.getElementById("clubsContent").innerText = "Erreur clubs XML"; 
//     return; 
//   }

//   const clubs = xml.getElementsByTagName("club");
//   let html = `<h2>${langTexts['listeClubs']}</h2><ul>`;

//   for (let club of clubs) {
//     const clubId = club.getAttribute("id");
//     const nom = club.getElementsByTagName("nom")[0].getAttribute(currentLang);
//     const description = club.getElementsByTagName("description")[0].getAttribute(currentLang);

//     html += `
//       <li>
//         <b>${nom}</b> - ${description}
//         <button onclick="afficherLogin(${clubId})">${langTexts['login']}</button>
//         <button onclick="afficherFormulaire(${clubId})">${langTexts['demande']}</button>
//       </li>
//     `;
//   }
//   html += "</ul>";
//   document.getElementById("clubsContent").innerHTML = html;
//   document.getElementById("formulaireClub").innerHTML = "";
// }

// function afficherFormulaire(clubId) {
//   const xml = loadXML("xml/clubs.xml");
//   const club = xml.querySelector(`club[id="${clubId}"]`);
//   const nom = club.getElementsByTagName("nom")[0].getAttribute(currentLang);

//   let formDiv = document.getElementById('formulaireClub');
//   formDiv.innerHTML = `
//     <h3>${langTexts['formulaireTitre']} ${nom}</h3>
//     <input id="nomEtudiant" placeholder="${langTexts['nomEtudiant']}" type="text" required>
//     <input id="emailEtudiant" placeholder="${langTexts['emailEtudiant'] || 'Email'}" type="email" required>
//     <textarea id="motivationEtudiant" placeholder="${langTexts['motivation'] || 'Motivation (optionnelle)'}" rows="4"></textarea>
//     <button onclick="envoyerDemandeAvecPHP(${clubId})">${langTexts['envoyer']}</button>
//     <button onclick="annulerFormulaire()">${langTexts['annuler'] || 'Annuler'}</button>
//   `;
// }

// function annulerFormulaire() {
//   document.getElementById("formulaireClub").innerHTML = "";
// }

// // ✅ NOUVELLE FONCTION : Envoyer au serveur PHP
// async function envoyerDemandeAvecPHP(clubId) {
//   const nom = document.getElementById("nomEtudiant").value.trim();
//   const email = document.getElementById("emailEtudiant").value.trim();
//   const motivation = document.getElementById("motivationEtudiant").value.trim();

//   // Validation
//   if (!nom) {
//     alert(langTexts['erreurNom'] || "Le nom est requis");
//     return;
//   }

//   if (!email || !validerEmail(email)) {
//     alert("Email invalide");
//     return;
//   }

//   // Créer la demande
//   const demande = {
//     id: "D" + Date.now(),
//     nomEtudiant: nom,
//     email: email,
//     motivation: motivation,
//     clubId: clubId,
//     dateCreation: new Date().toISOString()
//   };

//   try {
//     // Envoyer au serveur PHP
//     console.log("🔄 Envoi de la demande au serveur...");
//     console.log("URL:", window.location.origin + '/backend/sauvegarder_demande.php');
//     console.log("Données:", demande);
    
//     const response = await fetch('backend/sauvegarder_demande.php', {
//       method: 'POST',
//       headers: {
//         'Content-Type': 'application/json',
//       },
//       body: JSON.stringify(demande)
//     });

//     console.log("📡 Réponse HTTP:", response.status, response.statusText);

//     if (!response.ok) {
//       throw new Error(`Erreur HTTP ${response.status}: ${response.statusText}`);
//     }

//     const result = await response.json();
//     console.log("📦 Résultat:", result);

//     if (result.success) {
//       alert("✅ " + (langTexts['demandeEnvoyee'] || "Demande enregistrée dans demandes.xml !"));
//       document.getElementById("formulaireClub").innerHTML = "";
//       afficherNotification("✅ Demande enregistrée dans le XML!", "success");
//     } else {
//       throw new Error(result.message || "Erreur inconnue");
//     }
//   } catch (error) {
//     console.error("❌ Erreur complète:", error);
    
//     // Afficher l'erreur détaillée
//     const errorMessage = `
//       ❌ Erreur de connexion au serveur PHP
      
//       Détails: ${error.message}
      
//       Vérifiez:
//       1. Serveur PHP démarré (XAMPP/php -S)
//       2. URL correcte: ${window.location.href}
//       3. Fichier sauvegarder_demande.php existe
//       4. Permissions dossier xml/
      
//       ➡️ La demande sera sauvegardée en LOCAL
//     `;
    
//     console.warn(errorMessage);
//     alert(errorMessage);
    
//     // Fallback: sauvegarder dans localStorage
//     let demandes = JSON.parse(localStorage.getItem("demandes")) || [];
//     demandes.push(demande);
//     localStorage.setItem("demandes", JSON.stringify(demandes));
    
//     afficherNotification("💾 Sauvegardée localement (serveur indisponible)", "warning");
//     document.getElementById("formulaireClub").innerHTML = "";
//   }
// }

// // Validation d'email
// function validerEmail(email) {
//   const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//   return regex.test(email);
// }

// // Notification visuelle
// function afficherNotification(message, type = 'success') {
//   const notif = document.createElement('div');
//   notif.className = `notification ${type}`;
//   notif.textContent = message;
//   notif.style.cssText = `
//     position: fixed;
//     top: 20px;
//     right: 20px;
//     padding: 15px 25px;
//     background: ${type === 'success' ? '#4caf50' : '#f44336'};
//     color: white;
//     border-radius: 5px;
//     box-shadow: 0 4px 12px rgba(0,0,0,0.2);
//     z-index: 1000;
//     animation: slideIn 0.3s ease;
//   `;
  
//   document.body.appendChild(notif);
  
//   setTimeout(() => {
//     notif.style.animation = 'slideOut 0.3s ease';
//     setTimeout(() => notif.remove(), 300);
//   }, 3000);
// }

// // Ajouter les animations CSS
// const style = document.createElement('style');
// style.textContent = `
//   @keyframes slideIn {
//     from {
//       transform: translateX(400px);
//       opacity: 0;
//     }
//     to {
//       transform: translateX(0);
//       opacity: 1;
//     }
//   }
  
//   @keyframes slideOut {
//     from {
//       transform: translateX(0);
//       opacity: 1;
//     }
//     to {
//       transform: translateX(400px);
//       opacity: 0;
//     }
//   }
// `;
// document.head.appendChild(style);
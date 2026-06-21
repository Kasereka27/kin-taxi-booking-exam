/* ==========================================================================
   TaxiGo — app.js
   Logique commune : navigation, formulaires, données de démonstration.
   (Front-end seul — prêt à brancher sur une API back-end)
   ========================================================================== */

/* ----- Configuration API (à adapter côté back-end) ----- */
const API = {
    base: "/api/v1",
    endpoints: {
      login: "/auth/login",
      register: "/auth/register",
      rides: "/rides",
      track: "/rides/:id/track",
      drivers: "/drivers",
      payments: "/payments",
    },
  };
  
  /* ----- Menu mobile ----- */
  function toggleMenu() {
    const menu = document.getElementById("mobileMenu");
    if (menu) menu.classList.toggle("hidden");
  }
  
  /* ----- Marquer le lien actif dans la navbar ----- */
  document.addEventListener("DOMContentLoaded", () => {
    const page = location.pathname.split("/").pop() || "index.html";
    document.querySelectorAll(".nav-link").forEach((a) => {
      if (a.getAttribute("href") === page) {
        a.classList.remove("text-gray-600");
        a.classList.add("text-ink", "font-bold");
      }
    });
    document.getElementById("menuToggle")?.addEventListener("click", toggleMenu);
    initForms();
    initCarSelect();
    initEstimator();
  });
  
  /* ----- Gestion des formulaires (simulation) ----- */
  function initForms() {
    document.querySelectorAll("form[data-handler]").forEach((form) => {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const type = form.dataset.handler;
        const data = Object.fromEntries(new FormData(form).entries());
        handleForm(type, data, form);
      });
    });
  }
  
  function handleForm(type, data, form) {
    switch (type) {
      case "login":
        notify(form, "Connexion réussie ! Redirection...", "success");
        setTimeout(() => (location.href = "dashboard-client.html"), 1200);
        break;
      case "register":
        notify(form, "Compte créé avec succès ! Bienvenue chez TaxiGo.", "success");
        setTimeout(() => (location.href = "dashboard-client.html"), 1400);
        break;
      case "booking":
        notify(form, "Recherche d'un chauffeur disponible...", "info");
        setTimeout(() => (location.href = "suivi.html"), 1500);
        break;
      case "payment":
        notify(form, "Paiement accepté ! Reçu envoyé par e-mail.", "success");
        break;
      case "contact":
        notify(form, "Message envoyé ! Nous répondons sous 24h.", "success");
        form.reset();
        break;
      default:
        notify(form, "Action enregistrée.", "success");
    }
  }
  
  function notify(form, msg, type = "info") {
    let box = form.querySelector("[data-alert]");
    if (!box) {
      box = document.createElement("div");
      box.setAttribute("data-alert", "");
      form.prepend(box);
    }
    const styles =
      type === "success"
        ? "bg-green-100 text-green-700"
        : "bg-blue-100 text-blue-700";
    box.className = `mb-4 px-4 py-3 rounded-lg text-sm font-medium ${styles}`;
    box.textContent = msg;
    box.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
  
  /* ----- Sélecteur de véhicule (page réservation) ----- */
  function initCarSelect() {
    const options = document.querySelectorAll(".car-option");
    if (!options.length) return;
    const onCls = ["border-taxi", "bg-yellow-50", "ring-2", "ring-taxi"];
    options.forEach((opt) => {
      opt.addEventListener("click", () => {
        options.forEach((o) => o.classList.remove(...onCls));
        opt.classList.add(...onCls);
        const hidden = document.getElementById("vehicleType");
        if (hidden) hidden.value = opt.dataset.type;
        updateEstimate();
      });
    });
  }
  
  /* ----- Estimateur de prix ----- */
  const RATES = { eco: { base: 2.5, km: 1.1 }, confort: { base: 4, km: 1.6 }, van: { base: 6, km: 2.2 } };
  
  function initEstimator() {
    ["pickup", "dropoff"].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener("input", updateEstimate);
    });
    updateEstimate();
  }
  
  function updateEstimate() {
    const out = document.getElementById("estimate");
    if (!out) return;
    const type = document.getElementById("vehicleType")?.value || "eco";
    const distance = 8 + Math.random() * 6; // distance simulée (km)
    const r = RATES[type] || RATES.eco;
    const price = r.base + distance * r.km;
    const eta = Math.round(distance * 1.8 + 3);
    out.innerHTML = `
      <div class="flex justify-between"><span class="text-gray-500">Distance estimée</span><strong>${distance.toFixed(1)} km</strong></div>
      <div class="flex justify-between"><span class="text-gray-500">Durée estimée</span><strong>${eta} min</strong></div>
      <div class="flex justify-between text-xl mt-2"><span>Prix estimé</span><strong class="text-taxi-dark">${price.toFixed(2)} €</strong></div>`;
  }
  
  /* ----- Données de démonstration (pour les tableaux) ----- */
  const DEMO = {
    rides: [
      { id: "TG-1042", date: "18/06 14:30", from: "Gare Centrale", to: "Aéroport T2", driver: "Marc D.", price: "32,50 €", status: "Terminée" },
      { id: "TG-1041", date: "17/06 09:15", from: "Hôtel Lux", to: "Centre-ville", driver: "Sophie L.", price: "12,00 €", status: "Terminée" },
      { id: "TG-1040", date: "16/06 22:40", from: "Restaurant Belle", to: "Domicile", driver: "Karim B.", price: "18,75 €", status: "Annulée" },
      { id: "TG-1039", date: "15/06 07:50", from: "Domicile", to: "Bureau Tech", driver: "Marc D.", price: "9,90 €", status: "Terminée" },
    ],
  };
  
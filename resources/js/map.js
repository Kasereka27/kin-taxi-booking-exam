/* ==========================================================================
   KinTaxiBooking — map.js
   Suivi en temps réel (Google Maps ou Mapbox, Reverb + Echo).
   Provider : VITE_MAP_PROVIDER=google|mapbox
   ========================================================================== */

document.addEventListener("DOMContentLoaded", async () => {
  const container = document.getElementById("map");
  if (!container) return;

  const provider = (import.meta.env.VITE_MAP_PROVIDER ?? "google").toLowerCase();

  try {
    if (provider === "mapbox") {
      const { initMapboxTracking } = await import("./map-mapbox.js");
      initMapboxTracking(container);
    } else {
      const { initGoogleTracking } = await import("./map-google.js");
      await initGoogleTracking(container);
    }
  } catch (error) {
    console.error("Erreur initialisation carte:", error);
    container.innerHTML = `
      <div class="flex h-full min-h-[240px] items-center justify-center bg-red-50 p-8 text-center text-red-700">
        <p>Impossible de charger la carte. Vérifiez votre clé API et la console navigateur.</p>
      </div>`;
  }
});

export { buildRoute, setStep } from "./map-shared.js";

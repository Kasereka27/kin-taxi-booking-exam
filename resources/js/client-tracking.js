import { startRideGpsPublisher } from "./ride-gps-publisher.js";
import { getTrackingConsentPromise } from "./tracking-consent.js";

document.addEventListener("DOMContentLoaded", async () => {
  const data = window.trackingRide;

  if (!data?.isClient || !data?.clientTrackingUrl || !data.animate) {
    return;
  }

  const consented = await getTrackingConsentPromise();

  if (!consented) {
    return;
  }

  startRideGpsPublisher({
    trackingUrl: data.clientTrackingUrl,
    onActive: () => updateLiveStatus("Votre position est partagée", "bg-green-100 text-green-700"),
    onSimulation: () => updateLiveStatus("Position client · mode démo", "bg-amber-100 text-amber-800"),
    onError: () => updateLiveStatus("Localisation client indisponible", "bg-red-100 text-red-700"),
    getFallbackRoute: () => {
      const pickup = data.pickup ?? [-4.3217, 15.3125];

      return [pickup];
    },
  });
});

function updateLiveStatus(text, classes) {
  const el = document.getElementById("tracking-share-status");

  if (!el) {
    return;
  }

  el.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${classes}`;
  el.textContent = text;
  el.classList.remove("hidden");
}

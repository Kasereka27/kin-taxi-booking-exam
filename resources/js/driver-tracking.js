import { buildRoute } from "./map-shared.js";
import { startRideGpsPublisher } from "./ride-gps-publisher.js";
import { getTrackingConsentPromise } from "./tracking-consent.js";

document.addEventListener("DOMContentLoaded", async () => {
  const data = window.trackingRide;

  if (!data?.isDriver || !data?.trackingUrl || !data.animate) {
    return;
  }

  const consented = await getTrackingConsentPromise();

  if (!consented) {
    return;
  }

  const pickup = data.pickup ?? [-4.3217, 15.3125];
  const dropoff = data.dropoff ?? [-4.3017, 15.3325];
  const driverStart = data.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012];

  startRideGpsPublisher({
    trackingUrl: data.trackingUrl,
    onActive: () => updateLiveStatus("GPS chauffeur actif", "bg-green-100 text-green-700"),
    onSimulation: () => updateLiveStatus("GPS chauffeur · mode démo", "bg-amber-100 text-amber-800"),
    onError: () => updateLiveStatus("GPS chauffeur indisponible", "bg-red-100 text-red-700"),
    getRoutePath: () => data.routePath ?? data.bookedRoutePath,
    getFallbackRoute: () => {
      const pickup = data.pickup ?? [-4.3217, 15.3125];
      const dropoff = data.dropoff ?? [-4.3017, 15.3325];
      const driverStart = data.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012];

      return data.bookedRoutePath?.length
        ? data.bookedRoutePath
        : buildRoute(driverStart, pickup, dropoff, 60);
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

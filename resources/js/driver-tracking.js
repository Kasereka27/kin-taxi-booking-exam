import { buildRoute } from "./map-shared.js";

/* ==========================================================================
   KinTaxiBooking — driver-tracking.js
   Envoie la position GPS (simulée ou réelle) au serveur pour diffusion WebSocket.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  const data = window.trackingRide;

  if (!data?.isDriver || !data?.trackingUrl || !data.animate) {
    return;
  }

  if (data.routePath?.length) {
    waitForRoutePath(data, startDriverTracking);
    return;
  }

  startDriverTracking(data);
});

function waitForRoutePath(data, callback, attempts = 0) {
  if (data.routePath?.length || attempts > 40) {
    callback(data);
    return;
  }

  setTimeout(() => waitForRoutePath(data, callback, attempts + 1), 250);
}

function startDriverTracking(data) {
  const pickup = data.pickup ?? [-4.3217, 15.3125];
  const dropoff = data.dropoff ?? [-4.3017, 15.3325];
  const driverStart = data.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012];
  const route = data.routePath?.length
    ? data.routePath
    : buildRoute(driverStart, pickup, dropoff, 60);
  let index = 0;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";

  const sendPosition = (lat, lng) => {
    fetch(data.trackingUrl, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify({ lat, lng }),
      credentials: "same-origin",
    }).catch(() => {
      // ignore network errors during simulation
    });
  };

  sendPosition(route[0][0], route[0][1]);

  setInterval(() => {
    index = Math.min(index + 1, route.length - 1);
    const [lat, lng] = route[index];
    sendPosition(lat, lng);
  }, 3000);
}

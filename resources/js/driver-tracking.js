import { buildRoute } from "./map-shared.js";
import { getTrackingConsentPromise } from "./tracking-consent.js";

/* ==========================================================================
   KinTaxiBooking — driver-tracking.js
   Envoie la position GPS au serveur pour diffusion WebSocket (après consentement).
   ========================================================================== */

const MIN_SEND_INTERVAL_MS = 4000;
const MIN_MOVE_METERS = 12;

document.addEventListener("DOMContentLoaded", async () => {
  const data = window.trackingRide;

  if (!data?.isDriver || !data?.trackingUrl || !data.animate) {
    return;
  }

  const consented = await getTrackingConsentPromise();

  if (!consented) {
    return;
  }

  if (navigator.geolocation) {
    startRealGpsTracking(data);
    return;
  }

  startSimulatedTrackingWhenReady(data);
});

function startSimulatedTrackingWhenReady(data) {
  if (data.routePath?.length) {
    startSimulatedTracking(data);
    return;
  }

  waitForRoutePath(data, startSimulatedTracking);
}

function waitForRoutePath(data, callback, attempts = 0) {
  if (data.routePath?.length || attempts > 40) {
    callback(data);
    return;
  }

  setTimeout(() => waitForRoutePath(data, callback, attempts + 1), 250);
}

function startRealGpsTracking(data) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";
  const sendPosition = createPositionSender(data.trackingUrl, csrf);
  let lastLat = null;
  let lastLng = null;
  let lastSentAt = 0;

  const maybeSend = (lat, lng) => {
    const now = Date.now();
    const movedEnough =
      lastLat === null || distanceMeters(lastLat, lastLng, lat, lng) >= MIN_MOVE_METERS;
    const waitedEnough = now - lastSentAt >= MIN_SEND_INTERVAL_MS;

    if (!movedEnough && !waitedEnough) {
      return;
    }

    lastLat = lat;
    lastLng = lng;
    lastSentAt = now;
    sendPosition(lat, lng);
    updateDriverGpsStatus("active");
  };

  navigator.geolocation.getCurrentPosition(
    (position) => {
      maybeSend(position.coords.latitude, position.coords.longitude);
    },
    () => {
      updateDriverGpsStatus("error");
    },
    {
      enableHighAccuracy: true,
      maximumAge: 0,
      timeout: 15000,
    },
  );

  const watchId = navigator.geolocation.watchPosition(
    (position) => {
      maybeSend(position.coords.latitude, position.coords.longitude);
    },
    (error) => {
      updateDriverGpsStatus("error");

      if (error.code === error.PERMISSION_DENIED) {
        return;
      }

      startSimulatedTrackingWhenReady(data);
    },
    {
      enableHighAccuracy: true,
      maximumAge: 5000,
      timeout: 15000,
    },
  );

  window.addEventListener("beforeunload", () => {
    navigator.geolocation.clearWatch(watchId);
  });
}

function startSimulatedTracking(data) {
  updateDriverGpsStatus("simulation");

  const pickup = data.pickup ?? [-4.3217, 15.3125];
  const dropoff = data.dropoff ?? [-4.3017, 15.3325];
  const driverStart = data.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012];
  const route = data.routePath?.length
    ? data.routePath
    : buildRoute(driverStart, pickup, dropoff, 60);
  let index = 0;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";
  const sendPosition = createPositionSender(data.trackingUrl, csrf);

  sendPosition(route[0][0], route[0][1]);

  setInterval(() => {
    index = Math.min(index + 1, route.length - 1);
    const [lat, lng] = route[index];
    sendPosition(lat, lng);
  }, 3000);
}

function createPositionSender(trackingUrl, csrf) {
  return (lat, lng) => {
    fetch(trackingUrl, {
      method: "PATCH",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify({ lat, lng }),
      credentials: "same-origin",
    }).catch(() => {
      // ignore network errors during tracking
    });
  };
}

function distanceMeters(lat1, lng1, lat2, lng2) {
  const earthRadius = 6371000;
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLng = ((lng2 - lng1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLng / 2) ** 2;

  return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function updateDriverGpsStatus(mode) {
  const el = document.getElementById("tracking-live-status");

  if (!el) {
    return;
  }

  const labels = {
    active: ["GPS actif · position envoyée", "bg-green-100 text-green-700"],
    simulation: ["Mode démo · itinéraire simulé", "bg-amber-100 text-amber-800"],
    error: ["GPS indisponible", "bg-red-100 text-red-700"],
  };

  const [text, classes] = labels[mode] ?? labels.error;
  el.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${classes}`;
  el.textContent = text;
  el.classList.remove("hidden");
}

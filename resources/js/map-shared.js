import { getEcho } from "./echo.js";
import { getMarkerSvg } from "./map-markers.js";

/** @typedef {{ setLatLng: (latLng: [number, number]) => void }} DriverHandle */
/** @typedef {{ panTo: (latLng: [number, number]) => void, fitPoints: (points: [number, number][], padding?: number) => void }} MapAdapter */

/**
 * @returns {{ pickup: [number, number], dropoff: [number, number], driverStart: [number, number], shouldAnimate: boolean, status: string, rideId: number|null, useWebSocket: boolean }}
 */
export function readTrackingData(trackingEnabled = true) {
  const data = window.trackingRide ?? {};

  const pickup = /** @type {[number, number]} */ (data.pickup ?? [-4.3217, 15.3125]);
  const dropoff = /** @type {[number, number]} */ (data.dropoff ?? [-4.3017, 15.3325]);
  const driverStart = /** @type {[number, number]} */ (
    data.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012]
  );
  const baseAnimate = data.animate ?? true;

  return {
    pickup,
    dropoff,
    driverStart,
    shouldAnimate: baseAnimate && trackingEnabled,
    status: data.status ?? "assigned",
    rideId: data.rideId ?? null,
    useWebSocket: Boolean(trackingEnabled && data.rideId && import.meta.env.VITE_REVERB_APP_KEY),
  };
}

export function showMapSetupMessage(container, provider) {
  const keyName =
    provider === "mapbox" ? "VITE_MAPBOX_ACCESS_TOKEN" : "VITE_GOOGLE_MAPS_API_KEY";

  container.innerHTML = `
    <div class="flex h-full min-h-[240px] items-center justify-center bg-gray-100 p-8 text-center">
      <div class="max-w-md">
        <div class="flex justify-center mb-3 text-gray-400">${getMarkerSvg("map")}</div>
        <p class="font-bold text-ink">Carte ${provider === "mapbox" ? "Mapbox" : "Google Maps"} non configurée</p>
        <p class="text-sm text-gray-500 mt-2">Ajoutez <code class="bg-white px-1 rounded">${keyName}</code> dans votre fichier <code class="bg-white px-1 rounded">.env</code>, puis relancez Vite.</p>
      </div>
    </div>`;
}

/**
 * @param {Record<string, unknown>} payload
 * @param {{ driverMarker: DriverHandle, mapAdapter: MapAdapter, etaEl: HTMLElement|null, onStatusChange: ((status: string) => void)|null }} ctx
 */
export function applyTrackingUpdate(payload, ctx) {
  if (payload.lat == null || payload.lng == null) {
    return;
  }

  const lat = Number(payload.lat);
  const lng = Number(payload.lng);

  ctx.driverMarker.setLatLng([lat, lng]);
  ctx.mapAdapter.panTo([lat, lng]);

  if (ctx.etaEl && payload.eta_minutes != null) {
    ctx.etaEl.textContent = String(payload.eta_minutes);
  }

  const stage =
    payload.status === "course"
      ? "course"
      : payload.status === "approche" || payload.status === "assigned"
        ? "approche"
        : payload.status;

  if (typeof stage === "string") {
    setStep(stage);
  }

  if (ctx.onStatusChange && payload.status) {
    ctx.onStatusChange(String(payload.status));
  }
}

function updateLiveStatus(mode) {
  const el = document.getElementById("tracking-live-status");

  if (!el || window.trackingRide?.isDriver) {
    return;
  }

  const labels = {
    websocket: ["Suivi live · WebSocket", "bg-green-100 text-green-700"],
    polling: ["Suivi live · synchronisation", "bg-blue-100 text-blue-700"],
    offline: ["Suivi live indisponible", "bg-red-100 text-red-700"],
  };

  const [text, classes] = labels[mode] ?? labels.offline;
  el.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold ${classes}`;
  el.textContent = text;
  el.classList.remove("hidden");
}

async function fetchTrackingSnapshot(showUrl) {
  const response = await fetch(showUrl, {
    headers: { Accept: "application/json" },
    credentials: "same-origin",
  });

  if (!response.ok) {
    return null;
  }

  return response.json();
}

function startPolling(showUrl, ctx, intervalMs = 4000) {
  if (!showUrl || window.__ktbTrackingPollTimer) {
    return;
  }

  updateLiveStatus("polling");

  const poll = async () => {
    try {
      const payload = await fetchTrackingSnapshot(showUrl);

      if (payload) {
        applyTrackingUpdate(payload, ctx);
      }
    } catch {
      updateLiveStatus("offline");
    }
  };

  poll();
  window.__ktbTrackingPollTimer = window.setInterval(poll, intervalMs);
}

export function subscribeToTracking(rideId, driverMarker, mapAdapter, etaEl, onStatusChange = null) {
  const showUrl = window.trackingRide?.trackingShowUrl ?? null;
  const ctx = { driverMarker, mapAdapter, etaEl, onStatusChange };

  if (showUrl) {
    fetchTrackingSnapshot(showUrl)
      .then((payload) => {
        if (payload) {
          applyTrackingUpdate(payload, ctx);
        }
      })
      .catch(() => {});
  }

  const echo = getEcho();

  if (echo && rideId) {
    const channel = echo.private(`rides.${rideId}`);

    channel.listen(".tracking.updated", (payload) => {
      applyTrackingUpdate(payload, ctx);
      updateLiveStatus("websocket");
    });

    channel.error(() => {
      startPolling(showUrl, ctx);
    });

    if (echo.connector?.pusher?.connection) {
      echo.connector.pusher.connection.bind("connected", () => {
        updateLiveStatus("websocket");
      });

      echo.connector.pusher.connection.bind("unavailable", () => {
        startPolling(showUrl, ctx);
      });

      echo.connector.pusher.connection.bind("failed", () => {
        startPolling(showUrl, ctx);
      });
    }

    return;
  }

  if (showUrl) {
    startPolling(showUrl, ctx);
    return;
  }

  updateLiveStatus("offline");
}

export function buildRoute(a, b, c, steps) {
  const pts = [];
  const seg = (p1, p2, n) => {
    for (let k = 0; k <= n; k++) {
      const t = k / n;
      const jitter = (Math.random() - 0.5) * 0.0015;
      pts.push([p1[0] + (p2[0] - p1[0]) * t + jitter, p1[1] + (p2[1] - p1[1]) * t + jitter]);
    }
  };
  seg(a, b, Math.floor(steps / 2));
  seg(b, c, Math.ceil(steps / 2));
  return pts;
}

export function setStep(stage) {
  const steps = { pending: 0, approche: 1, course: 2, arrivee: 3 };
  const current = steps[stage] ?? 0;
  const base = "w-3.5 h-3.5 rounded-full shrink-0 ";
  document.querySelectorAll(".track-step").forEach((el, idx) => {
    const dot = el.querySelector(".track-dot");
    const line = el.querySelector(".track-line");
    if (dot) {
      if (idx < current) dot.className = base + "bg-green-500";
      else if (idx === current) dot.className = base + "bg-taxi animate-pulse";
      else dot.className = base + "bg-gray-300";
    }
    if (line) line.className = "track-line w-px flex-1 my-1 " + (idx < current ? "bg-green-500" : "bg-gray-200");
  });
  const statusEl = document.getElementById("ride-status");
  if (statusEl) {
    const labels = {
      pending: ["En attente d'un chauffeur", "bg-gray-100 text-gray-700"],
      approche: ["Chauffeur en route", "bg-blue-100 text-blue-700"],
      course: ["Course en cours", "bg-yellow-100 text-yellow-700"],
      arrivee: ["Arrivé à destination", "bg-green-100 text-green-700"],
    };
    const [txt, cls] = labels[stage] || ["En attente", "bg-gray-100 text-gray-700"];
    statusEl.className =
      "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold " + cls;
    statusEl.textContent = txt;
  }
}

export function startTrackingSession(
  mapAdapter,
  tracking,
  createDriverMarker,
  drawRoute,
  routeOverride = null,
  pickupIndex = null,
  onStatusChange = null,
) {
  const { pickup, dropoff, driverStart, shouldAnimate, status, rideId } = tracking;

  mapAdapter.addMarker(pickup, "pickup", "Point de prise en charge");
  mapAdapter.addMarker(dropoff, "dropoff", "Destination");

  if (!shouldAnimate) {
    mapAdapter.fitPoints([pickup, dropoff]);
    if (status === "pending") setStep("pending");
    return;
  }

  const driver = createDriverMarker(driverStart);
  const route = routeOverride ?? buildRoute(driverStart, pickup, dropoff, 60);

  if (typeof drawRoute === "function") {
    drawRoute(route);
  }

  mapAdapter.fitPoints(route.length ? route : [pickup, dropoff]);

  if (window.trackingRide) {
    window.trackingRide.routePath = route;
    if (pickupIndex != null) {
      window.trackingRide.routePickupIndex = pickupIndex;
    }
  }

  const etaEl = document.getElementById("eta-value");
  const initialStage =
    status === "course" ? "course" : status === "approche" ? "approche" : "assigned";
  setStep(initialStage === "assigned" ? "approche" : initialStage);

  if (rideId) {
    subscribeToTracking(rideId, driver, mapAdapter, etaEl, onStatusChange);
  }
}

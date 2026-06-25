import { getEcho } from "./echo.js";

/** @typedef {{ setLatLng: (latLng: [number, number]) => void }} DriverHandle */
/** @typedef {{ panTo: (latLng: [number, number]) => void, fitPoints: (points: [number, number][], padding?: number) => void }} MapAdapter */

/**
 * @returns {{ pickup: [number, number], dropoff: [number, number], driverStart: [number, number], shouldAnimate: boolean, status: string, rideId: number|null, useWebSocket: boolean }}
 */
export function readTrackingData() {
  const data = window.trackingRide ?? {};

  const pickup = /** @type {[number, number]} */ (data.pickup ?? [-4.3217, 15.3125]);
  const dropoff = /** @type {[number, number]} */ (data.dropoff ?? [-4.3017, 15.3325]);
  const driverStart = /** @type {[number, number]} */ (
    data.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012]
  );

  return {
    pickup,
    dropoff,
    driverStart,
    shouldAnimate: data.animate ?? true,
    status: data.status ?? "assigned",
    rideId: data.rideId ?? null,
    useWebSocket: Boolean(data.rideId && import.meta.env.VITE_REVERB_APP_KEY),
  };
}

export function showMapSetupMessage(container, provider) {
  const keyName =
    provider === "mapbox" ? "VITE_MAPBOX_ACCESS_TOKEN" : "VITE_GOOGLE_MAPS_API_KEY";

  container.innerHTML = `
    <div class="flex h-full min-h-[240px] items-center justify-center bg-gray-100 p-8 text-center">
      <div class="max-w-md">
        <p class="text-4xl mb-3">🗺️</p>
        <p class="font-bold text-ink">Carte ${provider === "mapbox" ? "Mapbox" : "Google Maps"} non configurée</p>
        <p class="text-sm text-gray-500 mt-2">Ajoutez <code class="bg-white px-1 rounded">${keyName}</code> dans votre fichier <code class="bg-white px-1 rounded">.env</code>, puis relancez Vite.</p>
      </div>
    </div>`;
}

export function subscribeToTracking(rideId, driverMarker, mapAdapter, etaEl, onStatusChange = null) {
  const echo = getEcho();

  if (!echo) {
    return;
  }

  echo.private(`rides.${rideId}`).listen(".tracking.updated", (payload) => {
    if (payload.lat == null || payload.lng == null) return;

    driverMarker.setLatLng([payload.lat, payload.lng]);
    mapAdapter.panTo([payload.lat, payload.lng]);

    if (etaEl && payload.eta_minutes != null) {
      etaEl.textContent = String(payload.eta_minutes);
    }

    const stage =
      payload.status === "course"
        ? "course"
        : payload.status === "approche" || payload.status === "assigned"
          ? "approche"
          : payload.status;

    if (stage) setStep(stage);

    if (onStatusChange && payload.status) {
      onStatusChange(payload.status);
    }
  });
}

export function runSimulation(driver, mapAdapter, route, etaEl, pickupIndex = null) {
  let i = 0;
  const totalSteps = route.length;
  const splitAt = pickupIndex ?? Math.floor(totalSteps / 3);

  const timer = setInterval(() => {
    if (i >= totalSteps) {
      clearInterval(timer);
      setStep("arrivee");
      if (etaEl) etaEl.textContent = "0";
      return;
    }

    driver.setLatLng(route[i]);
    mapAdapter.panTo(route[i]);

    const remaining = Math.max(0, Math.round(((totalSteps - i) / totalSteps) * 12));
    if (etaEl) etaEl.textContent = String(remaining);

    if (i < splitAt) setStep("approche");
    else if (i < totalSteps - 2) setStep("course");

    i++;
  }, 700);
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
  const { pickup, dropoff, driverStart, shouldAnimate, status, rideId, useWebSocket } = tracking;

  mapAdapter.addMarker(pickup, "📍", "Point de prise en charge");
  mapAdapter.addMarker(dropoff, "🏁", "Destination");

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

  if (useWebSocket && rideId) {
    subscribeToTracking(rideId, driver, mapAdapter, etaEl, onStatusChange);
    return;
  }

  runSimulation(driver, mapAdapter, route, etaEl, pickupIndex);
}

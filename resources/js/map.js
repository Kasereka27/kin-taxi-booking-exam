/* ==========================================================================
   KinTaxiBooking — map.js
   Suivi en temps réel du chauffeur (Leaflet + OpenStreetMap).
   Les données de course sont injectées via window.trackingRide (suivi.blade.php).
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  if (!document.getElementById("map") || typeof L === "undefined") return;
  initTracking();
});

function initTracking() {
  const data = window.trackingRide;

  const pickup = data?.pickup ?? [-4.3217, 15.3125];
  const dropoff = data?.dropoff ?? [-4.3017, 15.3325];
  const driverStart = data?.driver ?? [pickup[0] - 0.018, pickup[1] - 0.012];
  const shouldAnimate = data?.animate ?? true;
  const status = data?.status ?? "assigned";

  const map = L.map("map", { zoomControl: true }).setView(pickup, 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap",
    maxZoom: 19,
  }).addTo(map);

  const emoji = (e, s) =>
    L.divIcon({
      html: `<div style="font-size:${s}px;line-height:1;filter:drop-shadow(0 2px 3px rgba(0,0,0,.3))">${e}</div>`,
      className: "",
      iconSize: [s, s],
    });

  const carIcon = emoji("🚕", 30);
  const pinPickup = emoji("📍", 28);
  const pinDrop = emoji("🏁", 28);

  L.marker(pickup, { icon: pinPickup }).addTo(map).bindPopup("Point de prise en charge");
  L.marker(dropoff, { icon: pinDrop }).addTo(map).bindPopup("Destination");

  if (!shouldAnimate) {
    map.fitBounds(L.latLngBounds([pickup, dropoff]), { padding: [40, 40] });
    if (status === "pending") {
      setStep("pending");
    }
    return;
  }

  const driver = L.marker(driverStart, { icon: carIcon }).addTo(map).bindPopup("Votre chauffeur");
  const route = buildRoute(driverStart, pickup, dropoff, 60);
  L.polyline(route, { color: "#ffce00", weight: 5, opacity: 0.85 }).addTo(map);

  let i = 0;
  const etaEl = document.getElementById("eta-value");
  const totalSteps = route.length;
  const pickupIndex = Math.floor(totalSteps / 3);

  const initialStage = status === "course" ? "course" : status === "approche" ? "approche" : "assigned";
  setStep(initialStage === "assigned" ? "approche" : initialStage);

  const timer = setInterval(() => {
    if (i >= totalSteps) {
      clearInterval(timer);
      setStep("arrivee");
      if (etaEl) etaEl.textContent = "0";
      return;
    }
    driver.setLatLng(route[i]);
    map.panTo(route[i], { animate: true, duration: 0.5 });

    const remaining = Math.max(0, Math.round(((totalSteps - i) / totalSteps) * 12));
    if (etaEl) etaEl.textContent = remaining;

    if (i < pickupIndex) setStep("approche");
    else if (i < totalSteps - 2) setStep("course");

    i++;
  }, 700);
}

function buildRoute(a, b, c, steps) {
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

function setStep(stage) {
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
  const status = document.getElementById("ride-status");
  if (status) {
    const labels = {
      pending: ["En attente d'un chauffeur", "bg-gray-100 text-gray-700"],
      approche: ["Chauffeur en route", "bg-blue-100 text-blue-700"],
      course: ["Course en cours", "bg-yellow-100 text-yellow-700"],
      arrivee: ["Arrivé à destination", "bg-green-100 text-green-700"],
    };
    const [txt, cls] = labels[stage] || ["En attente", "bg-gray-100 text-gray-700"];
    status.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold " + cls;
    status.textContent = txt;
  }
}

const MIN_SEND_INTERVAL_MS = 4000;
const MIN_MOVE_METERS = 12;

/**
 * @param {{ trackingUrl: string, onActive?: () => void, onSimulation?: () => void, onError?: () => void, getRoutePath?: () => [number, number][]|undefined, getFallbackRoute?: () => [number, number][] }} options
 */
export function startRideGpsPublisher(options) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";
  const sendPosition = createPositionSender(options.trackingUrl, csrf);

  if (navigator.geolocation) {
    startRealGps(sendPosition, options);
    return;
  }

  startSimulatedRoute(sendPosition, options);
}

function startRealGps(sendPosition, options) {
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
    options.onActive?.();
  };

  navigator.geolocation.getCurrentPosition(
    (position) => {
      maybeSend(position.coords.latitude, position.coords.longitude);
    },
    () => {
      options.onError?.();
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
      options.onError?.();

      if (error.code === error.PERMISSION_DENIED) {
        return;
      }

      startSimulatedRoute(sendPosition, options);
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

function startSimulatedRoute(sendPosition, options) {
  options.onSimulation?.();

  const waitForRoute = (attempts = 0) => {
    const route = options.getRoutePath?.();

    if (route?.length || attempts > 40) {
      runRouteSimulation(sendPosition, route ?? options.getFallbackRoute?.() ?? []);
      return;
    }

    setTimeout(() => waitForRoute(attempts + 1), 250);
  };

  waitForRoute();
}

function runRouteSimulation(sendPosition, route) {
  if (!route.length) {
    return;
  }

  let index = 0;
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
    }).catch(() => {});
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

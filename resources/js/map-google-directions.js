/** @param {[number, number]} latLng */
export function toGoogleLatLng([lat, lng]) {
  return { lat, lng };
}

/** Style natif Google Maps (itinéraire actif). */
export const ACTIVE_ROUTE_STYLE = {
  strokeColor: "#1a73e8",
  strokeOpacity: 0.95,
  strokeWeight: 6,
};

/** Aperçu discret de la suite du trajet. */
export const PREVIEW_ROUTE_STYLE = {
  strokeColor: "#64748b",
  strokeOpacity: 0.35,
  strokeWeight: 4,
};

/**
 * @param {google.maps.DirectionsResult} result
 * @returns {{ path: [number, number][], pickupIndex: number }}
 */
export function pathFromDirectionsResult(result) {
  const legs = result.routes?.[0]?.legs ?? [];
  const path = [];

  for (const leg of legs) {
    for (const step of leg.steps ?? []) {
      for (const point of step.path ?? []) {
        path.push([point.lat(), point.lng()]);
      }
    }
  }

  if (path.length === 0 && result.routes?.[0]?.overview_path) {
    for (const point of result.routes[0].overview_path) {
      path.push([point.lat(), point.lng()]);
    }
  }

  return {
    path,
    pickupIndex: path.length,
  };
}

/**
 * @param {google.maps.DirectionsService} service
 * @param {[number, number]} origin
 * @param {[number, number]} destination
 */
export function requestDrivingRoute(service, origin, destination) {
  return new Promise((resolve, reject) => {
    service.route(
      {
        origin: toGoogleLatLng(origin),
        destination: toGoogleLatLng(destination),
        travelMode: google.maps.TravelMode.DRIVING,
        provideRouteAlternatives: false,
      },
      (result, directionsStatus) => {
        if (directionsStatus === google.maps.DirectionsStatus.OK && result) {
          resolve(result);
          return;
        }

        reject(new Error(directionsStatus ?? "DIRECTIONS_FAILED"));
      },
    );
  });
}

/**
 * @param {google.maps.Map} map
 */
export function createDirectionsRenderer(map) {
  return new google.maps.DirectionsRenderer({
    map,
    suppressMarkers: true,
    preserveViewport: true,
    draggable: false,
    // Style natif Google (contour blanc + tracé bleu) — pas de couleur taxi custom.
    polylineOptions: ACTIVE_ROUTE_STYLE,
  });
}

/**
 * @param {google.maps.DirectionsResult} result
 * @param {string} status
 */
export function applyDirectionsMeta(result, status) {
  const legs = result.routes?.[0]?.legs ?? [];
  const activeLeg = legs[0];

  if (!activeLeg) {
    return;
  }

  const minutes = Math.max(1, Math.round((activeLeg.duration?.value ?? 0) / 60));
  const etaEl = document.getElementById("eta-value");
  if (etaEl) {
    etaEl.textContent = String(minutes);
  }

  const distanceEl = document.getElementById("route-distance");
  if (distanceEl && activeLeg.distance?.text) {
    distanceEl.textContent = activeLeg.distance.text;
  }

  const routeLabel = document.getElementById("route-phase-label");
  if (routeLabel) {
    routeLabel.textContent =
      status === "course" ? "Itinéraire vers la destination" : "Itinéraire vers le point de prise en charge";
  }
}

/**
 * @param {google.maps.Map} map
 * @param {google.maps.DirectionsResult} result
 * @returns {google.maps.Polyline | null}
 */
export function drawPreviewPolyline(map, result) {
  const path = result.routes?.[0]?.overview_path ?? [];

  if (path.length === 0) {
    return null;
  }

  return new google.maps.Polyline({
    path,
    geodesic: true,
    map,
    ...PREVIEW_ROUTE_STYLE,
  });
}

/**
 * @param {{ driverStart: [number, number], pickup: [number, number], dropoff: [number, number], status: string }} tracking
 */
export function activeRouteEndpoints(tracking) {
  if (tracking.status === "course") {
    return { origin: tracking.pickup, destination: tracking.dropoff };
  }

  return { origin: tracking.driverStart, destination: tracking.pickup };
}

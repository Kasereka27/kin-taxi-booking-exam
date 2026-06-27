import { importLibrary, setOptions } from "@googlemaps/js-api-loader";
import {
  activeRouteEndpoints,
  applyDirectionsMeta,
  createDirectionsRenderer,
  drawPreviewPolyline,
  pathFromDirectionsResult,
  requestDrivingRoute,
  toGoogleLatLng,
} from "./map-google-directions.js";
import { getMarkerSvg } from "./map-markers.js";
import {
  buildRoute,
  readTrackingData,
  showMapSetupMessage,
  startTrackingSession,
} from "./map-shared.js";

/** @type {{ map: google.maps.Map, renderer: google.maps.DirectionsRenderer, service: google.maps.DirectionsService, previewLines: google.maps.Polyline[] } | null} */
let routeLayer = null;

/**
 * @param {google.maps.Map} map
 * @param {[number, number]} latLng
 * @param {'pickup'|'dropoff'|'driver'} markerType
 * @param {string} title
 */
function addSvgMarker(map, latLng, markerType, title) {
  return new google.maps.Marker({
    position: toGoogleLatLng(latLng),
    map,
    title,
    icon: {
      url: `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(getMarkerSvg(markerType))}`,
      scaledSize: new google.maps.Size(32, 32),
      anchor: new google.maps.Point(16, 32),
    },
    optimized: false,
  });
}

function clearPreviewLines() {
  if (!routeLayer) {
    return;
  }

  for (const line of routeLayer.previewLines) {
    line.setMap(null);
  }

  routeLayer.previewLines = [];
}

/**
 * @param {google.maps.Map} map
 * @param {ReturnType<typeof readTrackingData>} tracking
 */
async function renderGoogleRoute(map, tracking) {
  await importLibrary("routes");

  if (!routeLayer) {
    routeLayer = {
      map,
      renderer: createDirectionsRenderer(map),
      service: new google.maps.DirectionsService(),
      previewLines: [],
    };
  }

  clearPreviewLines();

  const { origin, destination } = activeRouteEndpoints(tracking);

  try {
    const activeResult = await requestDrivingRoute(routeLayer.service, origin, destination);
    routeLayer.renderer.setDirections(activeResult);
    applyDirectionsMeta(activeResult, tracking.status);

    if (tracking.status !== "course") {
      try {
        const previewResult = await requestDrivingRoute(
          routeLayer.service,
          tracking.pickup,
          tracking.dropoff,
        );
        const previewLine = drawPreviewPolyline(map, previewResult);
        if (previewLine) {
          routeLayer.previewLines.push(previewLine);
        }
      } catch {
        // Aperçu optionnel — on ignore si indisponible.
      }
    }

    const { path } = pathFromDirectionsResult(activeResult);

    return {
      path,
      pickupIndex: tracking.status === "course" ? 0 : path.length,
    };
  } catch (error) {
    console.warn("Directions API indisponible, tracé simplifié utilisé.", error);
    routeLayer.renderer.setDirections({ routes: [] });

    const fallback = buildRoute(tracking.driverStart, tracking.pickup, tracking.dropoff, 60);

    const fallbackLine = new google.maps.Polyline({
      path: fallback.map(toGoogleLatLng),
      geodesic: true,
      strokeColor: "#64748b",
      strokeOpacity: 0.5,
      strokeWeight: 4,
      map,
    });

    routeLayer.previewLines.push(fallbackLine);

    return { path: fallback, pickupIndex: Math.floor(fallback.length / 3) };
  }
}

/**
 * @param {ReturnType<typeof readTrackingData>} tracking
 */
async function refreshRouteForStatus(tracking) {
  if (!routeLayer || !tracking.shouldAnimate) {
    return;
  }

  const routeData = await renderGoogleRoute(routeLayer.map, tracking);

  if (window.trackingRide && routeData.path.length) {
    window.trackingRide.routePath = routeData.path;
  }
}

export async function initGoogleTracking(container, trackingEnabled = true) {
  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

  if (!apiKey) {
    showMapSetupMessage(container, "google");
    return;
  }

  setOptions({ key: apiKey, v: "weekly" });
  await importLibrary("maps");

  const tracking = readTrackingData(trackingEnabled);

  const map = new google.maps.Map(container, {
    center: toGoogleLatLng(tracking.pickup),
    zoom: 14,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
    zoomControl: true,
    gestureHandling: "greedy",
  });

  /** @type {import('./map-shared.js').MapAdapter} */
  const mapAdapter = {
    panTo(latLng) {
      map.panTo(toGoogleLatLng(latLng));
    },
    fitPoints(points, padding = 48) {
      if (points.length === 0) return;
      const bounds = new google.maps.LatLngBounds();
      points.forEach((pt) => bounds.extend(toGoogleLatLng(pt)));
      map.fitBounds(bounds, padding);
    },
    addMarker(latLng, markerType, title) {
      addSvgMarker(map, latLng, markerType, title);
    },
  };

  const routeData = tracking.shouldAnimate
    ? await renderGoogleRoute(map, tracking)
    : { path: [], pickupIndex: null };

  startTrackingSession(
    mapAdapter,
    tracking,
    (driverStart) => {
      const marker = addSvgMarker(map, driverStart, "driver", "Votre chauffeur");

      return {
        setLatLng([lat, lng]) {
          marker.setPosition(toGoogleLatLng([lat, lng]));
        },
      };
    },
    null,
    routeData.path.length ? routeData.path : null,
    routeData.pickupIndex,
    (newStatus) => {
      if (newStatus === "course") {
        refreshRouteForStatus({ ...readTrackingData(), status: "course" });
      }
    },
  );
}

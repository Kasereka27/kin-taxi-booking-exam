import { importLibrary, setOptions } from "@googlemaps/js-api-loader";
import {
  activeRouteEndpoints,
  applyDirectionsMeta,
  createDirectionsRenderer,
  drawPathPolyline,
  fetchBookedRoute,
  pathFromDirectionsResult,
  PREVIEW_ROUTE_STYLE,
  requestDrivingRoute,
  toGoogleLatLng,
} from "./map-google-directions.js";
import { getMarkerSvg } from "./map-markers.js";
import {
  readTrackingData,
  showMapSetupMessage,
  startTrackingSession,
} from "./map-shared.js";

/** @type {{ map: google.maps.Map, renderer: google.maps.DirectionsRenderer, service: google.maps.DirectionsService, bookedLine: google.maps.Polyline | null, previewLines: google.maps.Polyline[] } | null} */
let routeLayer = null;

/**
 * @param {google.maps.Map} map
 * @param {[number, number]} latLng
 * @param {'pickup'|'dropoff'|'driver'|'client'} markerType
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

  routeLayer.bookedLine?.setMap(null);
  routeLayer.bookedLine = null;

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
      bookedLine: null,
      previewLines: [],
    };
  }

  clearPreviewLines();

  let bookedPath = tracking.routePolyline ?? [];
  let bookedResult = null;

  if (!bookedPath.length) {
    try {
      const booked = await fetchBookedRoute(
        routeLayer.service,
        tracking.pickup,
        tracking.dropoff,
      );
      bookedPath = booked.path;
      bookedResult = booked.result;
    } catch (error) {
      console.warn("Itinéraire réservé indisponible via Directions API.", error);
    }
  }

  if (bookedPath.length) {
    routeLayer.bookedLine = drawPathPolyline(map, bookedPath, {
      strokeColor: "#64748b",
      strokeOpacity: 0.55,
      strokeWeight: 5,
    });

    if (routeLayer.bookedLine) {
      routeLayer.previewLines.push(routeLayer.bookedLine);
    }
  }

  const { origin, destination } = activeRouteEndpoints(tracking);

  try {
    if (tracking.status === "course") {
      const activeResult =
        bookedResult ??
        (await requestDrivingRoute(routeLayer.service, tracking.pickup, tracking.dropoff));
      routeLayer.renderer.setDirections(activeResult);
      applyDirectionsMeta(activeResult, tracking.status);

      return {
        bookedPath,
        simulationPath: pathFromDirectionsResult(activeResult).path,
        pickupIndex: 0,
      };
    }

    const approachResult = await requestDrivingRoute(routeLayer.service, origin, destination);
    routeLayer.renderer.setDirections(approachResult);
    applyDirectionsMeta(approachResult, tracking.status);

    const approachPath = pathFromDirectionsResult(approachResult).path;

    return {
      bookedPath,
      simulationPath: approachPath.length ? approachPath : bookedPath,
      pickupIndex: approachPath.length,
    };
  } catch (error) {
    console.warn("Directions API indisponible, tracé simplifié utilisé.", error);
    routeLayer.renderer.setDirections({ routes: [] });

    if (!bookedPath.length) {
      bookedPath = [tracking.pickup, tracking.dropoff];
      routeLayer.bookedLine = drawPathPolyline(map, bookedPath, PREVIEW_ROUTE_STYLE);

      if (routeLayer.bookedLine) {
        routeLayer.previewLines.push(routeLayer.bookedLine);
      }
    }

    return {
      bookedPath,
      simulationPath: bookedPath,
      pickupIndex: 0,
    };
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

  if (window.trackingRide) {
    window.trackingRide.routePath = routeData.simulationPath;
    window.trackingRide.bookedRoutePath = routeData.bookedPath;
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
      if (points.length === 0) {
        return;
      }

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
    : { bookedPath: [], simulationPath: [], pickupIndex: null };

  if (window.trackingRide) {
    window.trackingRide.bookedRoutePath = routeData.bookedPath;
  }

  mapAdapter.fitPoints(
    routeData.bookedPath.length
      ? [...routeData.bookedPath, tracking.pickup, tracking.dropoff]
      : [tracking.pickup, tracking.dropoff],
  );

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
    routeData.simulationPath.length ? routeData.simulationPath : null,
    routeData.pickupIndex,
    (newStatus) => {
      if (newStatus === "course") {
        refreshRouteForStatus({ ...readTrackingData(), status: "course" });
      }
    },
    (clientStart) => {
      const marker = addSvgMarker(map, clientStart, "client", "Client");

      return {
        setLatLng([lat, lng]) {
          marker.setPosition(toGoogleLatLng([lat, lng]));
        },
      };
    },
  );
}

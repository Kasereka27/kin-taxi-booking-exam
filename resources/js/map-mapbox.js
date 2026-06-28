import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";
import { createMarkerElement } from "./map-markers.js";
import { drawMapboxRouteLayer, fetchMapboxBookedRoute } from "./map-mapbox-directions.js";
import { buildRoute, readTrackingData, showMapSetupMessage, startTrackingSession } from "./map-shared.js";

/** @param {[number, number]} latLng [lat, lng] */
function toLngLat([lat, lng]) {
  return [lng, lat];
}

/**
 * @param {ReturnType<typeof readTrackingData>} tracking
 * @param {string} token
 */
async function resolveBookedPath(tracking, token) {
  if (tracking.routePolyline?.length) {
    return tracking.routePolyline;
  }

  try {
    const booked = await fetchMapboxBookedRoute(tracking.pickup, tracking.dropoff, token);

    const distanceEl = document.getElementById("route-distance");
    if (distanceEl) {
      distanceEl.textContent = `${booked.distanceKm.toFixed(1)} km`;
    }

    const etaEl = document.getElementById("eta-value");
    if (etaEl && tracking.status === "course") {
      etaEl.textContent = String(booked.durationMinutes);
    }

    return booked.path;
  } catch (error) {
    console.warn("Itinéraire Mapbox indisponible, tracé simplifié utilisé.", error);

    return buildRoute(tracking.driverStart, tracking.pickup, tracking.dropoff, 40);
  }
}

export function initMapboxTracking(container, trackingEnabled = true) {
  const token = import.meta.env.VITE_MAPBOX_ACCESS_TOKEN;

  if (!token) {
    showMapSetupMessage(container, "mapbox");
    return;
  }

  mapboxgl.accessToken = token;

  const tracking = readTrackingData(trackingEnabled);
  const style =
    import.meta.env.VITE_MAPBOX_STYLE ?? "mapbox://styles/mapbox/streets-v12";

  const map = new mapboxgl.Map({
    container,
    style,
    center: toLngLat(tracking.pickup),
    zoom: 13.5,
    attributionControl: true,
  });

  map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), "top-right");

  map.on("load", async () => {
    const bookedPath = tracking.shouldAnimate
      ? await resolveBookedPath(tracking, token)
      : [];

    if (bookedPath.length) {
      drawMapboxRouteLayer(map, bookedPath, "ride-booked-route", "ride-booked-route-line", "#64748b");
    }

    /** @type {import('./map-shared.js').MapAdapter} */
    const mapAdapter = {
      panTo(latLng) {
        map.easeTo({ center: toLngLat(latLng), duration: 500 });
      },
      fitPoints(points, padding = 48) {
        if (points.length === 0) {
          return;
        }

        const bounds = points.reduce(
          (b, pt) => b.extend(toLngLat(pt)),
          new mapboxgl.LngLatBounds(toLngLat(points[0]), toLngLat(points[0])),
        );
        map.fitBounds(bounds, { padding, duration: 0 });
      },
      addMarker(latLng, markerType, title) {
        const marker = new mapboxgl.Marker({ element: createMarkerElement(markerType) })
          .setLngLat(toLngLat(latLng))
          .addTo(map);
        if (title) {
          marker.setPopup(
            new mapboxgl.Popup({ offset: 20, closeButton: false }).setText(title),
          );
        }
      },
    };

    if (window.trackingRide) {
      window.trackingRide.bookedRoutePath = bookedPath;
    }

    startTrackingSession(
      mapAdapter,
      tracking,
      (driverStart) => {
        const marker = new mapboxgl.Marker({ element: createMarkerElement("driver") })
          .setLngLat(toLngLat(driverStart))
          .setPopup(
            new mapboxgl.Popup({ offset: 20, closeButton: false }).setText("Votre chauffeur"),
          )
          .addTo(map);

        return {
          setLatLng([lat, lng]) {
            marker.setLngLat(toLngLat([lat, lng]));
          },
        };
      },
      null,
      bookedPath.length ? bookedPath : null,
      null,
      null,
      (clientStart) => {
        const marker = new mapboxgl.Marker({ element: createMarkerElement("client") })
          .setLngLat(toLngLat(clientStart))
          .setPopup(
            new mapboxgl.Popup({ offset: 20, closeButton: false }).setText("Client"),
          )
          .addTo(map);

        return {
          setLatLng([lat, lng]) {
            marker.setLngLat(toLngLat([lat, lng]));
          },
        };
      },
    );
  });
}

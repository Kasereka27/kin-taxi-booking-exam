import mapboxgl from "mapbox-gl";
import "mapbox-gl/dist/mapbox-gl.css";
import { createMarkerElement } from "./map-markers.js";
import { readTrackingData, showMapSetupMessage, startTrackingSession } from "./map-shared.js";

/** @param {[number, number]} latLng [lat, lng] */
function toLngLat([lat, lng]) {
  return [lng, lat];
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

  map.on("load", () => {
    /** @type {import('./map-shared.js').MapAdapter} */
    const mapAdapter = {
      panTo(latLng) {
        map.easeTo({ center: toLngLat(latLng), duration: 500 });
      },
      fitPoints(points, padding = 48) {
        if (points.length === 0) return;
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
      (route) => {
        map.addSource("ride-route", {
          type: "geojson",
          data: {
            type: "Feature",
            properties: {},
            geometry: {
              type: "LineString",
              coordinates: route.map(toLngLat),
            },
          },
        });

        map.addLayer({
          id: "ride-route-line",
          type: "line",
          source: "ride-route",
          layout: { "line-join": "round", "line-cap": "round" },
          paint: {
            "line-color": "#ffce00",
            "line-width": 6,
            "line-opacity": 0.9,
          },
        });
      },
    );
  });
}

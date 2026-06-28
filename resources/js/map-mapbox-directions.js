/** @param {[number, number]} latLng [lat, lng] */
function toLngLat([lat, lng]) {
  return [lng, lat];
}

/**
 * @param {[number, number]} origin [lat, lng]
 * @param {[number, number]} destination [lat, lng]
 * @param {string} token
 * @returns {Promise<{ path: [number, number][], distanceKm: number, durationMinutes: number }>}
 */
export async function fetchMapboxBookedRoute(origin, destination, token) {
  const coordinates = `${toLngLat(origin).join(",")};${toLngLat(destination).join(",")}`;
  const url = new URL(
    `https://api.mapbox.com/directions/v5/mapbox/driving/${coordinates}`,
  );
  url.searchParams.set("geometries", "geojson");
  url.searchParams.set("overview", "full");
  url.searchParams.set("access_token", token);

  const response = await fetch(url);

  if (!response.ok) {
    throw new Error("MAPBOX_DIRECTIONS_FAILED");
  }

  const payload = await response.json();
  const route = payload.routes?.[0];

  if (!route?.geometry?.coordinates?.length) {
    throw new Error("MAPBOX_DIRECTIONS_EMPTY");
  }

  const path = route.geometry.coordinates.map(
    ([lng, lat]) => /** @type {[number, number]} */ ([lat, lng]),
  );

  return {
    path,
    distanceKm: Math.round((route.distance ?? 0) / 100) / 10,
    durationMinutes: Math.max(1, Math.round((route.duration ?? 0) / 60)),
  };
}

/**
 * @param {mapboxgl.Map} map
 * @param {[number, number][]} path
 * @param {string} sourceId
 * @param {string} layerId
 * @param {string} color
 */
export function drawMapboxRouteLayer(map, path, sourceId, layerId, color) {
  if (!path.length) {
    return;
  }

  if (map.getSource(sourceId)) {
    map.getSource(sourceId).setData({
      type: "Feature",
      properties: {},
      geometry: {
        type: "LineString",
        coordinates: path.map(toLngLat),
      },
    });

    return;
  }

  map.addSource(sourceId, {
    type: "geojson",
    data: {
      type: "Feature",
      properties: {},
      geometry: {
        type: "LineString",
        coordinates: path.map(toLngLat),
      },
    },
  });

  map.addLayer({
    id: layerId,
    type: "line",
    source: sourceId,
    layout: { "line-join": "round", "line-cap": "round" },
    paint: {
      "line-color": color,
      "line-width": 6,
      "line-opacity": 0.9,
    },
  });
}

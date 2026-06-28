const MARKER_SVGS = {
  pickup: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FFCE00" width="32" height="32"><path fill-rule="evenodd" d="M11.54 22.351h.01a.75.75 0 0 0 .73-.437l4.5-9.75a.75.75 0 0 0-.73-1.064H7.04a.75.75 0 0 0-.73 1.064l4.5 9.75a.75.75 0 0 0 .73.437ZM12 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/></svg>`,
  dropoff: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1a1a2e" width="32" height="32"><path fill-rule="evenodd" d="M3 3.75a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 3.75Zm0 4.5a.75.75 0 0 1 .75-.75h16.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 8.25Zm0 4.5a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Zm0 4.5a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/></svg>`,
  client:
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#2563eb" width="32" height="32"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/></svg>`,
  driver: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#FFCE00" width="36" height="36"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>`,
  map: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#6b7280" stroke-width="1.5" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>`,
};

/**
 * @param {'pickup'|'dropoff'|'driver'|'client'|'map'} type
 */
export function getMarkerSvg(type) {
  return MARKER_SVGS[type] ?? MARKER_SVGS.pickup;
}

/**
 * @param {'pickup'|'dropoff'|'driver'|'client'|'map'} type
 * @returns {HTMLDivElement}
 */
export function createMarkerElement(type) {
  const el = document.createElement("div");
  el.style.lineHeight = "0";
  el.style.filter = "drop-shadow(0 2px 4px rgba(0,0,0,.35))";
  el.innerHTML = getMarkerSvg(type);
  return el;
}

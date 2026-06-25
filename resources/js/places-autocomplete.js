import { importLibrary, setOptions } from "@googlemaps/js-api-loader";
import { estimateRidePrice, RIDE_RATES } from "./ride-rates.js";

/** @type {{ south: number, west: number, north: number, east: number }} */
export const KINSHASA_BOUNDS = {
  south: -4.6,
  west: 15.0,
  north: -4.2,
  east: 15.6,
};

/**
 * @param {number} lat1
 * @param {number} lng1
 * @param {number} lat2
 * @param {number} lng2
 */
export function distanceKmBetween(lat1, lng1, lat2, lng2) {
  const earthRadius = 6371;
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLng = ((lng2 - lng1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLng / 2) ** 2;

  return Math.round(earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)) * 10) / 10;
}

/**
 * @param {HTMLFormElement} form
 */
function showAddressAlert(form, message) {
  const alertBox = form.querySelector("[data-address-alert]");

  if (alertBox) {
    alertBox.textContent = message;
    alertBox.classList.remove("hidden");
  }
}

/**
 * @param {number} lat
 * @param {number} lng
 */
function isWithinKinshasa(lat, lng) {
  return (
    lat >= KINSHASA_BOUNDS.south &&
    lat <= KINSHASA_BOUNDS.north &&
    lng >= KINSHASA_BOUNDS.west &&
    lng <= KINSHASA_BOUNDS.east
  );
}

/**
 * @param {HTMLUListElement} list
 */
function hidePredictions(list) {
  list.hidden = true;
  list.replaceChildren();
}

/**
 * @param {HTMLInputElement} input
 * @param {HTMLInputElement} latInput
 * @param {HTMLInputElement} lngInput
 * @param {google.maps.places.AutocompleteService} autocompleteService
 * @param {google.maps.places.PlacesService} placesService
 * @param {() => void} onChange
 */
function bindCustomAutocomplete(
  input,
  latInput,
  lngInput,
  autocompleteService,
  placesService,
  onChange,
) {
  const host = input.closest("[data-address-input]");
  if (!(host instanceof HTMLElement)) {
    return;
  }

  const list = document.createElement("ul");
  list.className =
    "absolute left-0 right-0 top-full z-[200] mt-1 max-h-60 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg";
  list.hidden = true;
  host.appendChild(list);

  let sessionToken = new google.maps.places.AutocompleteSessionToken();
  let debounceTimer = 0;
  let activeRequest = 0;

  const predictionBounds = {
    east: KINSHASA_BOUNDS.east,
    west: KINSHASA_BOUNDS.west,
    north: KINSHASA_BOUNDS.north,
    south: KINSHASA_BOUNDS.south,
  };

  const clearCoordinates = () => {
    latInput.value = "";
    lngInput.value = "";
    input.setCustomValidity("");
    onChange();
  };

  const selectPlace = (placeId, label) => {
    const requestId = ++activeRequest;

    placesService.getDetails(
      {
        placeId,
        fields: ["formatted_address", "geometry", "name"],
        sessionToken,
      },
      (place, status) => {
        if (requestId !== activeRequest) {
          return;
        }

        sessionToken = new google.maps.places.AutocompleteSessionToken();
        hidePredictions(list);

        if (status !== google.maps.places.PlacesServiceStatus.OK || !place?.geometry?.location) {
          clearCoordinates();
          return;
        }

        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();

        if (!isWithinKinshasa(lat, lng)) {
          clearCoordinates();
          input.setCustomValidity("Choisissez une adresse située à Kinshasa.");
          input.reportValidity();
          return;
        }

        input.value = place.formatted_address ?? place.name ?? label;
        latInput.value = String(lat);
        lngInput.value = String(lng);
        input.setCustomValidity("");
        onChange();
      },
    );
  };

  const renderPredictions = (predictions) => {
    list.replaceChildren();

    predictions.forEach((prediction) => {
      const item = document.createElement("li");
      item.className =
        "cursor-pointer px-4 py-2.5 text-sm text-ink hover:bg-yellow-50 border-b border-gray-100 last:border-b-0";
      item.textContent = prediction.description;
      item.addEventListener("mousedown", (event) => {
        event.preventDefault();
        selectPlace(prediction.place_id, prediction.description);
      });
      list.appendChild(item);
    });

    list.hidden = false;
  };

  input.addEventListener("input", () => {
    clearCoordinates();
    window.clearTimeout(debounceTimer);

    const query = input.value.trim();
    if (query.length < 2) {
      hidePredictions(list);
      return;
    }

    debounceTimer = window.setTimeout(() => {
      const requestId = ++activeRequest;

      autocompleteService.getPlacePredictions(
        {
          input: query,
          sessionToken,
          bounds: predictionBounds,
          componentRestrictions: { country: "cd" },
        },
        (predictions, status) => {
          if (requestId !== activeRequest) {
            return;
          }

          if (
            status !== google.maps.places.PlacesServiceStatus.OK ||
            !predictions?.length
          ) {
            hidePredictions(list);
            return;
          }

          renderPredictions(predictions);
        },
      );
    }, 280);
  });

  input.addEventListener("blur", () => {
    window.setTimeout(() => hidePredictions(list), 180);
  });

  input.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      hidePredictions(list);
    }
  });
}

/**
 * @param {HTMLFormElement} form
 */
export async function initBookingPlaces(form) {
  const apiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

  if (!apiKey) {
    showAddressAlert(
      form,
      "Autocomplétion indisponible : ajoutez VITE_GOOGLE_MAPS_API_KEY dans votre fichier .env, puis relancez npm run dev.",
    );

    return;
  }

  window.gm_authFailure = () => {
    showAddressAlert(
      form,
      "Google Maps refuse la clé API. Vérifiez dans Google Cloud : facturation active, « Maps JavaScript API » + « Places API » activées, et http://localhost:8000/* autorisé dans les restrictions HTTP de la clé.",
    );
  };

  try {
    setOptions({ key: apiKey, v: "weekly" });
    await importLibrary("places");
  } catch (error) {
    console.error("Google Places", error);
    showAddressAlert(
      form,
      "Impossible de charger Google Places. Activez « Maps JavaScript API » et « Places API » sur Google Cloud.",
    );

    return;
  }

  const pickupInput = /** @type {HTMLInputElement | null} */ (form.querySelector("#pickup"));
  const dropoffInput = /** @type {HTMLInputElement | null} */ (form.querySelector("#dropoff"));
  const pickupLat = /** @type {HTMLInputElement | null} */ (form.querySelector("#pickup_lat"));
  const pickupLng = /** @type {HTMLInputElement | null} */ (form.querySelector("#pickup_lng"));
  const dropoffLat = /** @type {HTMLInputElement | null} */ (form.querySelector("#dropoff_lat"));
  const dropoffLng = /** @type {HTMLInputElement | null} */ (form.querySelector("#dropoff_lng"));

  if (!pickupInput || !dropoffInput || !pickupLat || !pickupLng || !dropoffLat || !dropoffLng) {
    return;
  }

  const autocompleteService = new google.maps.places.AutocompleteService();
  const placesService = new google.maps.places.PlacesService(document.createElement("div"));

  const updateEstimate = () => {
    const estimate = document.getElementById("estimate");
    const vehicleType = document.getElementById("vehicleType")?.value || "eco";

    if (!estimate || !pickupLat.value || !pickupLng.value || !dropoffLat.value || !dropoffLng.value) {
      return;
    }

    const distance = distanceKmBetween(
      Number(pickupLat.value),
      Number(pickupLng.value),
      Number(dropoffLat.value),
      Number(dropoffLng.value),
    );
    const price = estimateRidePrice(vehicleType, distance);
    const rate = RIDE_RATES[vehicleType] ?? RIDE_RATES.eco;
    const eta = Math.round(distance * 1.8 + 3);

    estimate.innerHTML = `
      <div class="flex justify-between"><span class="text-gray-500">Distance estimée</span><strong>${distance.toFixed(1)} km</strong></div>
      <div class="flex justify-between"><span class="text-gray-500">Durée estimée</span><strong>${eta} min</strong></div>
      <div class="flex justify-between text-sm text-gray-400"><span>Tarif</span><span>${rate.base.toLocaleString("fr-FR")} FC + ${rate.km.toLocaleString("fr-FR")} FC/km</span></div>
      <div class="flex justify-between text-xl mt-2"><span>Prix estimé</span><strong class="text-taxi-dark">${price.toLocaleString("fr-FR")} FC</strong></div>`;
  };

  bindCustomAutocomplete(pickupInput, pickupLat, pickupLng, autocompleteService, placesService, updateEstimate);
  bindCustomAutocomplete(dropoffInput, dropoffLat, dropoffLng, autocompleteService, placesService, updateEstimate);

  document.getElementById("vehicleType")?.addEventListener("change", updateEstimate);
  document.querySelectorAll(".car-option").forEach((option) => {
    option.addEventListener("click", () => window.setTimeout(updateEstimate, 0));
  });

  form.addEventListener("submit", (event) => {
    if (!pickupLat.value || !pickupLng.value || !dropoffLat.value || !dropoffLng.value) {
      event.preventDefault();

      showAddressAlert(
        form,
        "Choisissez le départ et la destination dans les suggestions (Kinshasa).",
      );

      form.querySelector("[data-address-alert]")?.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }
  });

  updateEstimate();
}

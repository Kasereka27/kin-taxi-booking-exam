import { initBookingPlaces } from "./places-autocomplete.js";

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("booking-form");

  if (form instanceof HTMLFormElement) {
    initBookingPlaces(form);
  }
});

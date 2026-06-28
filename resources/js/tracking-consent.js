/* ==========================================================================
   KinTaxiBooking — tracking-consent.js
   Consentement explicite avant activation du suivi GPS / temps réel.
   ========================================================================== */

const STORAGE_PREFIX = "ktb-tracking-consent:";

const MESSAGES = {
  driver:
    "Pour permettre au client de suivre la course en direct, KinTaxiBooking doit accéder à la position GPS de votre appareil pendant cette course. Vos coordonnées sont transmises de façon sécurisée et uniquement le temps de la course active.",
  client:
    "Pour suivre la course en direct, KinTaxiBooking doit accéder à la position GPS de votre appareil. Vos coordonnées seront partagées avec votre chauffeur et vous recevrez la position du chauffeur en temps réel jusqu'à la fin de la course.",
};

/**
 * @returns {boolean}
 */
export function hasTrackingConsent(rideId) {
  if (!rideId) {
    return false;
  }

  return sessionStorage.getItem(`${STORAGE_PREFIX}${rideId}`) === "granted";
}

/**
 * @returns {Promise<boolean>}
 */
export function ensureTrackingConsent(options = {}) {
  const data = window.trackingRide ?? {};
  const rideId = options.rideId ?? data.rideId;
  const isDriver = options.isDriver ?? data.isDriver ?? false;
  const needsConsent = options.needsConsent ?? (Boolean(data.animate) && Boolean(rideId));

  if (!needsConsent || !rideId) {
    return Promise.resolve(true);
  }

  if (hasTrackingConsent(rideId)) {
    return Promise.resolve(true);
  }

  if (sessionStorage.getItem(`${STORAGE_PREFIX}${rideId}`) === "declined") {
    showDeclinedNotice();
    return Promise.resolve(false);
  }

  return showConsentModal(rideId, isDriver);
}

/**
 * @returns {Promise<boolean>}
 */
function showConsentModal(rideId, isDriver) {
  return new Promise((resolve) => {
    const modal = document.getElementById("tracking-consent-modal");

    if (!modal) {
      resolve(false);
      return;
    }

    const messageEl = document.getElementById("tracking-consent-message");
    const acceptBtn = document.getElementById("tracking-consent-accept");
    const declineBtn = document.getElementById("tracking-consent-decline");

    if (messageEl) {
      messageEl.textContent = isDriver ? MESSAGES.driver : MESSAGES.client;
    }

    const cleanup = () => {
      modal.classList.add("hidden");
      modal.setAttribute("aria-hidden", "true");
      acceptBtn?.removeEventListener("click", onAccept);
      declineBtn?.removeEventListener("click", onDecline);
    };

    const onAccept = () => {
      sessionStorage.setItem(`${STORAGE_PREFIX}${rideId}`, "granted");
      cleanup();
      window.dispatchEvent(
        new CustomEvent("ktb:tracking-consent-granted", { detail: { rideId } }),
      );
      resolve(true);
    };

    const onDecline = () => {
      sessionStorage.setItem(`${STORAGE_PREFIX}${rideId}`, "declined");
      cleanup();
      showDeclinedNotice();
      resolve(false);
    };

    acceptBtn?.addEventListener("click", onAccept);
    declineBtn?.addEventListener("click", onDecline);

    modal.classList.remove("hidden");
    modal.setAttribute("aria-hidden", "false");
    acceptBtn?.focus();
  });
}

function showDeclinedNotice() {
  document.getElementById("tracking-consent-declined")?.classList.remove("hidden");
}

/**
 * @returns {Promise<boolean>}
 */
export function getTrackingConsentPromise() {
  if (!window.__ktbConsentPromise) {
    window.__ktbConsentPromise = ensureTrackingConsent();
  }

  return window.__ktbConsentPromise;
}

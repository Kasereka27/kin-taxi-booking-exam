import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

/**
 * Initialise Laravel Echo (Reverb) si les variables Vite sont présentes.
 */
export function createEcho() {
  const key = import.meta.env.VITE_REVERB_APP_KEY;

  if (!key) {
    return null;
  }

  const scheme = import.meta.env.VITE_REVERB_SCHEME ?? "http";
  const port = Number(import.meta.env.VITE_REVERB_PORT ?? (scheme === "https" ? 443 : 8090));

  return new Echo({
    broadcaster: "reverb",
    key,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === "https",
    enabledTransports: ["ws", "wss"],
    authEndpoint: "/broadcasting/auth",
    auth: {
      headers: {
        "X-CSRF-TOKEN":
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "",
      },
    },
  });
}

export function getEcho() {
  if (!window.__ktbEcho) {
    window.__ktbEcho = createEcho();
  }

  return window.__ktbEcho;
}

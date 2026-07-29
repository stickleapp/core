/**
 * What should this do?
 *
 * We could potentially build it on the server.
 *
 * Use echo.
 *
 * - Show an alert.
 *
 *
 *
 */

import Echo from "laravel-echo";

import Pusher from "pusher-js";
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

import Stickle from "stickle-client";

window.Stickle = new Stickle({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

// Stickle's events broadcast on private channels, so subscriptions go through
// /broadcasting/auth and are authorized against the viewStickle ability.
// Echo.private(), not Echo.channel(): a public subscription is never
// authorized, and no Stickle event is published on one.
window.Echo.private("stickle.firehose").listenToAll((eventName, event) => {
    console.log(eventName, event);
});

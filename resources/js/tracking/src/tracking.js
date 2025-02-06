(function () {
    const trackingUrl = "/stickle/api/track";

    function sendData(data) {
        navigator.sendBeacon(
            trackingUrl,
            JSON.stringify({
                payload: [data],
                _token: "%_CSRF_TOKEN_%",
            })
        );
    }

    function page() {
        const data = {
            type: "page",
            url: window.location.href,
            timestamp: new Date().toISOString(),
        };
        sendData(data);
    }

    function track(eventName, eventData = {}) {
        const data = {
            type: "track",
            name: eventName,
            data: eventData,
            timestamp: new Date().toISOString(),
        };
        sendData(data);
    }

    window.stickle = {
        page,
        track,
    };

    // Automatically track page view on load
    window.addEventListener("load", page);

    function trackSPA() {
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;

        history.pushState = function () {
            originalPushState.apply(this, arguments);
            page();
        };

        history.replaceState = function () {
            originalReplaceState.apply(this, arguments);
            page();
        };

        window.addEventListener("popstate", page);
    }

    trackSPA();
})();

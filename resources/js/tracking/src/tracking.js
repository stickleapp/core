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

    function page(pageName = "", properties = {}) {
        const data = {
            type: "page",
            name: pageName,
            properties: properties || {},
            timestamp: new Date().toISOString(),
        };
        sendData(data);
    }

    function track(eventName, properties = {}) {
        const data = {
            type: "track",
            name: eventName,
            properties: properties || {},
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

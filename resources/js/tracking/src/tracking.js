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

    function page(e, properties = {}) {
        const data = {
            type: "page",
            properties: {
                name: document.title,
                url: window.location.href,
                title: document.title,
                referrer: document.referrer,
                user_agent: navigator.userAgent,
            },
            timestamp: new Date().toISOString(),
        };
        sendData(data);
    }

    function track(eventName, properties = {}) {
        properties.name = eventName;
        const data = {
            type: "track",
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

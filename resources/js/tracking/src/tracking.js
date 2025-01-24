(function () {
    const trackingUrl = "/STICKLE-track";

    function sendTrackingData(data) {
        navigator.sendBeacon(
            trackingUrl,
            JSON.stringify({
                payload: [data],
                _token: "%_CSRF_TOKEN_%",
            })
        );
    }

    function trackPageView() {
        const data = {
            type: "page",
            url: window.location.href,
            timestamp: new Date().toISOString(),
        };
        sendTrackingData(data);
    }

    function trackEvent(eventName, eventData = {}) {
        const data = {
            type: "track",
            name: eventName,
            data: eventData,
            timestamp: new Date().toISOString(),
        };
        sendTrackingData(data);
    }

    window.stickle = {
        trackPageView,
        trackEvent,
    };

    // Automatically track page view on load
    window.addEventListener("load", trackPageView);

    function trackSPA() {
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;

        history.pushState = function () {
            originalPushState.apply(this, arguments);
            trackPageView();
        };

        history.replaceState = function () {
            originalReplaceState.apply(this, arguments);
            trackPageView();
        };

        window.addEventListener("popstate", trackPageView);
    }

    trackSPA();
})();

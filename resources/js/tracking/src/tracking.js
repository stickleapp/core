(function () {
    const trackingUrl = "/cascade-track";

    function sendTrackingData(data) {
        data["_token"] = "%_CSRF_TOKEN_%";
        navigator.sendBeacon(trackingUrl, JSON.stringify(data));
    }

    function trackPageView() {
        const data = {
            type: "pageview",
            url: window.location.href,
            timestamp: new Date().toISOString(),
        };
        sendTrackingData(data);
    }

    function trackEvent(eventName, eventData = {}) {
        const data = {
            type: "event",
            name: eventName,
            data: eventData,
            timestamp: new Date().toISOString(),
        };
        sendTrackingData(data);
    }

    window.Tracking = {
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

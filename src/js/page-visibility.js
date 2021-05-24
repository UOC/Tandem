var Visibility = (function() {

    var hidden,
        visibilitychange,
        state;

    // Set the property and event names
    if (typeof document.hidden !== "undefined") {
        hidden = "hidden";
        visibilitychange = "visibilitychange";
        state = "visibilityState";
    } else if (typeof document.mozHidden !== "undefined") {
        hidden = "mozHidden";
        visibilitychange = "mozvisibilitychange";
        state = "mozVisibilityState";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilitychange = "msvisibilitychange";
        state = "msVisibilityState";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilitychange = "webkitvisibilitychange";
        state = "webkitVisibilityState";
    }

    /**
     * Determine whether or not window is hidden.
     *
     * This will return false if the browser does
     *    not support visibility. That means it will return
     *    that the window is visible.
     */
    function isHidden() {
        return document[hidden] || false;
    }

    return {
        isHidden: isHidden,
        state: state,
        hidden: hidden, // string name of the "hidden" property
        visibilitychange: visibilitychange // string name of the "visibilitychange" event
    };

})();
let currentRequest = null; // Global variable to store the ongoing request

export function downloadUrl(url, callback) {
    // If there's an ongoing request, abort it
    if (currentRequest) {
        currentRequest.abort();
    }

    // Create a new request
    currentRequest = window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;

    currentRequest.onreadystatechange = function () {
        if (currentRequest.readyState === 4) {
            currentRequest.onreadystatechange = () => {}; // Do nothing
            callback(currentRequest.responseText, currentRequest.status);
        }
    };

    currentRequest.open('POST', url, true);
    currentRequest.send();
}

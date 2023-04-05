export function downloadUrl(url, body, callback) {
    let request = window.ActiveXObject ? new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            request.onreadystatechange = () => {}; // Do nothing
            callback(request.responseText, request.status);
        }
    };

    request.open('POST', url, true);
    request.send(body);
}

export const loadingSpinnerHTML = '<div class="loader"></div>';

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

export function decodeHtmlSpecialChars(text) {
    let map = {
        '&amp;': '&',
        '&#038;': "&",
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'",
        '&#8217;': "’",
        '&#8216;': "‘",
        '&#8211;': "–",
        '&#8212;': "—",
        '&#8230;': "…",
        '&#8221;': '”'
    };

    return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
}

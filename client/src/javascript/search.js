import Fuse from "fuse.js";
import {decodeHtmlSpecialChars, downloadUrl} from "./utils";

let locale = document.querySelector('html').getAttribute('lang');
locale = locale.replace('-', '_');
const path = './_resources/search/';
const fileName = path + locale + '.json';

document.addEventListener("DOMContentLoaded", async function (e) {

    const searchButton = document.querySelector('button#search-btn');
    if(!searchButton) return;

    const searchInput = document.querySelector('input#search-pattern');
    if(!searchInput) return;

    const list = await getData();
    const fuse = new Fuse(list, getOptions());

    searchInput.addEventListener('keyup', () => {
        handleSearch(fuse, list, searchInput.value)
    });

    searchButton.addEventListener('click', () => {
        handleSearch(fuse, list, searchInput.value)
    });
});

function handleSearch(fuse, list, searchValue) {
    let result = fuse.search(searchValue);

    let items = result.filter(item => {
        return item.score < 0.5;
    }).map((item) => {
        return item.item;
    });

    let url = 'api/search/result?locale=' + locale;

    downloadUrl(url, JSON.stringify(items),(response) => {
        let resultElement = document.getElementById('js-result-list');
        if(!resultElement) return;

        resultElement.textContent = '';
        resultElement.innerHTML = response;
    })
}

function getOptions() {

    let keys = ['title'];

    let customKeys = document.querySelector('#search-index-keys');
    if(customKeys) {
        customKeys = decodeHtmlSpecialChars(customKeys.value);
        keys = JSON.parse(customKeys);
    }

    return {
        isCaseSensitive: false,
        includeScore: true,
        shouldSort: true,
        // includeMatches: false,
        // findAllMatches: false,
        minMatchCharLength: 2,
        // location: 0,
        threshold: 0.5,
        // distance: 100,
        // useExtendedSearch: false,
        // ignoreLocation: false,
        // ignoreFieldNorm: false,
        // fieldNormWeight: 1,
        keys: keys
    };
}

async function getData() {
    return await fetch(fileName)
        .then(response => response.json())
        .then(json => { return json });
}

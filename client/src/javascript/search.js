import Fuse from "fuse.js";
import {decodeHtmlSpecialChars, downloadUrl} from "./utils";
let locale = document.querySelector('html').getAttribute('lang');
locale = locale.replace('-', '_');

let fuse = null;
let isInlineSearch = false;

document.addEventListener("DOMContentLoaded", async function (e) {

    const searchButton = document.querySelector('button#search-btn');
    if(!searchButton) return;

    const searchInput = document.querySelector('input#search-pattern');
    if(!searchInput) return;

    isInlineSearch = !!document.querySelector('#inline-search');

    const list = await fetchData(locale);
    if(!list) return;

    fuse = new Fuse(list, getOptions());

    initURLSearch(searchInput);

    searchInput.addEventListener('keyup', () => {
        handleSearch(searchInput.value);
    });

    searchButton.addEventListener('click', () => {
        handleSearch(searchInput.value);
    });
});

function initURLSearch(searchInput) {
    let initSearch = window.location.search;
    if(!initSearch) return;

    let value = initSearch.split('=')[1];

    handleSearch(value);
    searchInput.value = value;
}

function handleSearch(searchValue) {

    let result = fuse.search(searchValue);

    let items = result.filter(item => {
        return item.score < 0.3;
    }).map((item) => {
        return item.item;
    });

    let url = 'api/search/result?inline=' + isInlineSearch
    if(locale) url += '&locale=' + locale;

    // Max items to display on inline search 10
    if(isInlineSearch) {
        items = items.slice(0,10);
    }

    downloadUrl(url, JSON.stringify(items),(response) => {
        let resultElement = document.getElementById('js-result-list');
        if(!resultElement) return;

        resultElement.textContent = '';
        resultElement.innerHTML = response;

        if(resultElement.classList.contains('hidden')) {
            resultElement.classList.remove('hidden');
        }

        // Update Read More Link with search Value
        let readMoreLink = document.querySelector('a#search-see-more');
        if(readMoreLink) readMoreLink.search = `?value=${searchValue}`;
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

async function fetchData(filename) {
    const path = './_resources/search/' + filename + '.json';

    return await fetch(path)
        .then(async (res) => {
            if (res.ok) {
                return await res.json();
            } else if (res.status === 404 && locale !== 'index') {
                return await fetchData('index')
            }
            return null;
        });
}

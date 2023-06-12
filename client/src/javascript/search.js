import Fuse from "fuse.js";
import {decodeHtmlSpecialChars, downloadUrl} from "./utils";
let locale = document.querySelector('html').getAttribute('lang');
locale = locale.replace('-', '_');

let fuse = null;
let failedToDataFetch = false;
let isInlineSearch = false;
let loader = null;

document.addEventListener("DOMContentLoaded", async function (e) {

    const searchInput = document.querySelector('input#search-pattern');
    if(!searchInput) return;

    isInlineSearch = !!document.querySelector('#inline-search');
    loader = document.querySelector('.search-loader');

    await initURLSearch(searchInput);

    searchInput.addEventListener('keyup', async () => {
        await handleSearch(searchInput.value);
    });
});

async function initFuse() {
    const list = await fetchData(locale);
    if (!list) return;

    fuse = new Fuse(await list, getOptions());
}

async function initURLSearch(searchInput) {
    const queryString = window.location.search;
    if(!queryString) return;

    const urlParams = new URLSearchParams(queryString);
    let value = urlParams.get('value');

    if(!value) return;
    await handleSearch(value);
    searchInput.value = value;
}

async function handleSearch(searchValue) {
    if(searchValue.length < 2) {
        let resultElement = document.getElementById('js-result-list');
        if(!resultElement) return;

        resultElement.textContent = '';
        resultElement.innerHTML = '';

        resultElement.classList.add('hidden');
        return;
    }

    if(!fuse) await initFuse();
    if(!fuse) return;

    if(loader) loader.classList.remove('hidden');
    // https://fusejs.io/examples.html#extended-search
    // Search with "include-match"
    let result = fuse.search("'" + searchValue);

    let items = result.filter(item => {
        return item.score < 0.5;
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

        if(loader) loader.classList.add('hidden');
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
        // includeMatches: true,
        // findAllMatches: true,
        minMatchCharLength: 2,
        // location: 0,
        threshold: 0.5,
        // distance: 100,
        useExtendedSearch: true, // https://fusejs.io/examples.html#extended-search
        ignoreLocation: true,
        // ignoreFieldNorm: false,
        // fieldNormWeight: 1,
        keys: keys
    };
}

async function fetchData(filename) {
    if(failedToDataFetch) return null;
    const path = './_resources/search/' + filename + '.json';
    const shouldRetryOnFail = filename !== 'index' && !failedToDataFetch;

    return await fetch(path, { cache: "force-cache" }).then(async (res) => {
            if (res.ok) {
                return await res.json();
            } else if (res.status === 404) {
                return await handleFetchError(shouldRetryOnFail);
            }
        }).catch(async () => {
            return await handleFetchError(shouldRetryOnFail);
        });
}

async function handleFetchError(retry = false) {
    if(retry) return await fetchData('index');

    failedToDataFetch = true;
    console.error('No search index found!');
    return null;
}

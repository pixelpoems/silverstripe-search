import {downloadUrl} from "./utils";
let locale = document.querySelector('html').getAttribute('lang');
locale = locale.replace('-', '_');

let isInlineSearch = false;
let loader = null;

document.addEventListener("DOMContentLoaded", async function (e) {

    let searchBars = document.querySelectorAll('.search-holder');

    for (const searchBar of searchBars) {
        const searchInput = searchBar.querySelector('input.search-pattern');
        if(!searchInput) continue;

        isInlineSearch = !!searchBar.querySelector('.inline-search');
        loader = searchBar.querySelector('.search-loader');

        await initURLSearch(searchInput, searchBar);

        searchInput.addEventListener('keyup', async () => {
            await handleSearch(searchInput.value, searchBar);
        });
    }
});

async function initURLSearch(searchInput, searchBar) {
    const queryString = window.location.search;
    if(!queryString) return;

    const urlParams = new URLSearchParams(queryString);
    let value = urlParams.get('value');

    if(!value) return;
    await handleSearch(value, searchBar);
    searchInput.value = value;
}

async function handleSearch(searchValue, searchBar) {
    let resultElement = searchBar.querySelector('.js-result-list');
    if(!resultElement) {
        resultElement = document.querySelector('.js-result-list');
        if(!resultElement) return;
    }

    if(searchValue.length < 2) {
        resultElement.textContent = '';
        resultElement.innerHTML = '';
        resultElement.classList.add('hidden');
        return;
    }

    if(loader) loader.classList.remove('hidden');

    let url = 'api/search/result?value=' + searchValue + '&inline=' + isInlineSearch
    if(locale) url += '&locale=' + locale;

    downloadUrl(url,(response) => {
        resultElement.textContent = '';
        resultElement.innerHTML = response;

        if(resultElement.classList.contains('hidden')) {
            resultElement.classList.remove('hidden');
        }

        // Update Read More Link with search Value
        let readMoreLink = document.querySelector('a.search-see-more');
        if(readMoreLink) readMoreLink.search = `?value=${searchValue}`;

        if(loader) loader.classList.add('hidden');
    })
}

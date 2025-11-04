import {downloadUrl} from "./utils";
let locale = document.querySelector('html').getAttribute('lang');
locale = locale.replace('-', '_');

let isInlineSearch = false;

document.addEventListener("DOMContentLoaded", async function (e) {

    let searchBars = document.querySelectorAll('.search-holder');

    for (const searchBar of searchBars) {
        const searchInput = searchBar.querySelector('input.search-input');
        if(!searchInput) continue;

        isInlineSearch = !!searchBar.querySelector('.search-result__inline');
        let loader = searchBar.querySelector('.search-loader');

        if(isInlineSearch) {
            const openBtn = searchBar.querySelector('.btn__search');

            initToggles();

            function initToggles() {
                let smToggle = searchBar.dataset.searchToggleSm === 'true';
                let lgToggle = searchBar.dataset.searchToggleLg === 'true';
                let SMLGBreakpoint = searchBar.dataset.searchToggleBreakpoint;

                console.log('smToggle: ' + smToggle);
                console.log('lgToggle: ' + lgToggle);
                openBtn.setAttribute('aria-hidden', 'true');
                openBtn.disabled = true;
                initToggle(smToggle, 'sm');
                initToggle(lgToggle, 'lg');

                let anyToggleActive = smToggle || lgToggle;
                if(!anyToggleActive) {
                    // No toggles active, no need to add event listeners

                    // Remove button on search bar - static so it doesn't toggle
                    openBtn.setAttribute('aria-hidden', 'true');
                    openBtn.disabled = true;
                    return;
                }

                // Add resize listener to handle toggles
                window.addEventListener('resize', function (e) {
                    console.log(window.innerWidth);
                    // On resize, reset and re-init toggles
                    searchBar.classList.remove('toggle');
                    openBtn.disabled = true;
                    openBtn.setAttribute('aria-hidden', 'true');

                    initToggle(smToggle, 'sm');
                    initToggle(lgToggle, 'lg');
                });

                function initToggle(toggle, toggleWidth) {

                    if (toggle) {
                        if (toggleWidth === 'sm' && window.innerWidth < parseInt(SMLGBreakpoint)) {
                            console.log('SM toggle is active and should be shown')
                            // SM toggle is active and should be shown
                            searchBar.classList.add('toggle');
                            openBtn.setAttribute('aria-hidden', 'false');
                            openBtn.disabled = false;
                        }

                        if (toggleWidth === 'lg' && window.innerWidth >= parseInt(SMLGBreakpoint)) {
                            console.log('LG toggle is active and should be shown')
                            // LG toggle is active and should be shown
                            searchBar.classList.add('toggle');
                            openBtn.setAttribute('aria-hidden', 'false');
                            openBtn.disabled = false;
                        }
                    }
                }
            }
        }

        await initURLSearch(searchInput, searchBar);

        searchInput.addEventListener('keyup', async (event) => {
            // Check if the key passed is ESC key
            if (event.key === 'Escape' || event.keyCode === 27) {
                searchInput.value = '';
                let resultElement = searchBar.querySelector('.js-result-list');
                if(resultElement) {
                    resultElement.textContent = '';
                    resultElement.innerHTML = '';
                    resultElement.classList.add('hidden');
                }

                return;
            }

            await handleSearch(searchInput.value, searchBar, loader);
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

function clearSearchResults(resultElement, searchInput) {
    if(resultElement) {
        resultElement.textContent = '';
        resultElement.innerHTML = '';
        resultElement.classList.add('hidden');
    }
    if(searchInput) {
        searchInput.value = '';
    }
}

async function handleSearch(searchValue, searchBar, loader) {
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

            // Add click-outside listener when results are shown
            setTimeout(() => {
                const handleClickOutside = (event) => {
                    // Check if click is outside searchBar and resultElement
                    if (!searchBar.contains(event.target) && !resultElement.contains(event.target)) {
                        clearSearchResults(resultElement, searchBar.querySelector('input.search-input'));
                        document.removeEventListener('click', handleClickOutside);
                    }
                };

                document.addEventListener('click', handleClickOutside);
            }, 0);
        }

        // Update Read More Link with search Value
        let readMoreLink = document.querySelector('a.search-result__more');
        if(readMoreLink) readMoreLink.search = `?value=${searchValue}`;

        if(loader) loader.classList.add('hidden');
    })
}

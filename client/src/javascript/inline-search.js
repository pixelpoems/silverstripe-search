document.addEventListener("DOMContentLoaded", () => {
    let openBtns = document.querySelectorAll('.btn__search');
    if(openBtns) {
        openBtns.forEach(openBtn => {
            openBtn.addEventListener('click', (e) => {
                let searchBars = document.querySelectorAll('.search-holder__inline');
                searchBars.forEach((searchBar) => {
                    let search = searchBar.querySelector('.search-bar');
                    let searchInput = search.querySelector('.search-input');
                    if(!searchInput) return;
                    search.classList.add('active');
                    searchInput.setAttribute('tabindex', '0');
                    searchInput.focus();

                    let closeBtn = search.querySelector('.btn__close');
                    if(closeBtn) {
                        closeBtn.setAttribute('tabindex', '0');

                        closeBtn.addEventListener('click', () => {
                            search.classList.remove('active');
                            openBtn.style.display = 'inline-block';
                            searchInput.setAttribute('tabindex', '-1');
                            closeBtn.setAttribute('tabindex', '-1');
                        })
                    }
                })
            });
        });
    }

    let closeBtns = document.querySelectorAll('.btn__close');
    if(closeBtns) {
        closeBtns.forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                let searchInputs = document.querySelectorAll('.search-input');
                searchInputs.forEach(input => {
                    input.value = '';
                })

                let resultElements = document.querySelectorAll('.js-result-list');
                resultElements.forEach(el => {
                    el.innerHTML = '';
                    el.classList.add('hidden');
                });
            })
        })
    }
});

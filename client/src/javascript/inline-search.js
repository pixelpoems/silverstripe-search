document.addEventListener("DOMContentLoaded", () => {
    let openBtns = document.querySelectorAll('.btn__search');

    openBtns.forEach(openBtn => {
        openBtn.addEventListener('click', (e) => {
            let searchBars = document.querySelectorAll('.search-holder__inline');

            searchBars.forEach((searchBar) => {
                let search = searchBar.querySelector('.search-bar__inline');
                let searchInput = search.querySelector('.search-input');
                if(!searchInput) return;

                search.classList.add('active');
                // openBtn.style.display = 'none';
                searchInput.focus();

                let closeBtn = search.querySelector('.btn__close');
                if(closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        search.classList.remove('active');
                        openBtn.style.display = 'inline-block';
                        searchInput.value = '';

                        let resultElement = searchBar.querySelector('.js-result-list');
                        resultElement.innerHTML = '';
                        resultElement.classList.add('hidden');
                    })
                }
            })
        });
    });

});

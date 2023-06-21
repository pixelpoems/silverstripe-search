document.addEventListener("DOMContentLoaded", () => {
    let openBtns = document.querySelectorAll('.open-inline-search-btn');

    openBtns.forEach(openBtn => {
        openBtn.addEventListener('click', (e) => {
            let searchBars = document.querySelectorAll('.inline-search-holder');

            searchBars.forEach((searchBar) => {
                let search = searchBar.querySelector('.inline-search');
                let searchInput = search.querySelector('.search-pattern');
                if(!searchInput) return;

                search.classList.remove('hidden');
                openBtn.style.display = 'none';
                searchInput.focus();

                let closeBtn = search.querySelector('.close-inline-search-btn');
                if(closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        search.classList.add('hidden');
                        openBtn.style.display = 'inline-block';
                        searchInput.value = '';
                    })
                }
            })
        });
    });

});

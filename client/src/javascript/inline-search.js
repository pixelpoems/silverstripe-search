document.addEventListener("DOMContentLoaded", () => {
    let openBtn = document.querySelector('#open-inline-search-btn');
    if(!openBtn) return;

    openBtn.addEventListener('click', (e) => {
        let search = document.querySelector('#inline-search');
        if(!search) return;

        search.classList.remove('hidden');
        openBtn.style.display = 'none';
        document.querySelector('#search-pattern').focus();

        let closeBtn = search.querySelector('#close-inline-search-btn');
        if(closeBtn) {
            closeBtn.addEventListener('click', () => {
                search.classList.add('hidden');
                openBtn.style.display = 'inline-block';
            })
        }
    });
});

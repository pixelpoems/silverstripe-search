<article>
    <div class="container">

        <h1>$Title</h1>

        <div class="search-bar">
            <input id="search-pattern">
            <button id="search-btn">Search</button>
        </div>

        <% if not $isDoctor %>
            <div class="search-docmsg">
                <%t Pixelpoems\FuseSearch\Pages\SearchPage.NotLoggedInfo 'You may not see all the search results, as some search results can only be displayed in the signed in area. If you are a practising healthcare professional, please log in <button class="" onclick="{link}">HERE</button> to see all search results.' link="window.docLogin.showModal();" %>
            </div>
        <% end_if %>
        <div id="js-result-list">
            <div></div>
        </div>
    </div>

    <input type="hidden" id="search-index-keys" value="$SearchKeys" />

</article>

<% if $ClassName != 'Pixelpoems\FuseSearch\Pages\SearchPage' %>
    <% require javascript("pixelpoems/silverstripe-fuse-search:client/dist/javascript/search.min.js") %>
    <% require javascript("pixelpoems/silverstripe-fuse-search:client/dist/javascript/inline-search.min.js") %>
    <% require css("pixelpoems/silverstripe-fuse-search:client/dist/css/search.min.css") %>

    <li>
        <div id="inline-search" class="search-bar hidden">
            <input id="search-pattern">
            <button id="search-btn">Search</button>
            <button id="close-inline-search-btn">Close</button>

            <div id="js-result-list" class="inline-search">
                <div></div>
            </div>
        </div>

        <button id="open-inline-search-btn" class="btn">
            OPEN SEARCH
        </button>
    </li>
<% end_if %>




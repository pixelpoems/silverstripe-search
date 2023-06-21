<% if $ClassName != 'Pixelpoems\Search\Pages\SearchPage' %>
    <% require javascript("pixelpoems/silverstripe-search:client/dist/javascript/search.min.js") %>
    <% require javascript("pixelpoems/silverstripe-search:client/dist/javascript/inline-search.min.js") %>
    <% require css("pixelpoems/silverstripe-search:client/dist/css/search.min.css") %>

    <li class="search-holder inline-search-holder">
        <div class="inline-search search-bar hidden">
            <div class="search-input-container">
                <input class="search-pattern" type="text" placeholder="Search"/>
                <div class="search-loader hidden">
                    <i class="loader"></i>
                </div>
            </div>

            <button class="close-inline-search-btn">Close</button>

            <div class="js-result-list inline-search hidden"></div>
        </div>

        <button class="open-inline-search-btn btn">
            OPEN SEARCH
        </button>
    </li>
<% end_if %>




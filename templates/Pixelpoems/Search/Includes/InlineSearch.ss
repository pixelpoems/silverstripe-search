<% if $ClassName != 'Pixelpoems\Search\Pages\SearchPage' %>
    <% require javascript("pixelpoems/silverstripe-search:client/dist/javascript/search.min.js") %>
    <% require javascript("pixelpoems/silverstripe-search:client/dist/javascript/inline-search.min.js") %>
    <% require css("pixelpoems/silverstripe-search:client/dist/css/search.min.css") %>

    <li>
        <div id="inline-search" class="search-bar hidden">
            <div  class="search-input-container">
                <input id="search-pattern" type="text" placeholder="Search"/>
                <div class="search-loader hidden">
                    <i class="loader"></i>
                </div>
            </div>

            <button id="close-inline-search-btn">Close</button>

            <div id="js-result-list" class="inline-search hidden"></div>
        </div>

        <button id="open-inline-search-btn" class="btn">
            OPEN SEARCH
        </button>
    </li>
<% end_if %>




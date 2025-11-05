<% if $ClassName != 'Pixelpoems\Search\Pages\SearchPage' %>
    <% require javascript("pixelpoems/silverstripe-search:client/dist/javascript/search.min.js") %>
    <% require javascript("pixelpoems/silverstripe-search:client/dist/javascript/inline-search.min.js") %>
    <% require css("pixelpoems/silverstripe-search:client/dist/css/search.min.css" , "") %>

    <div class="search-holder search-holder__inline" $SearchToggleAttr>
        <div class="search-input-container" role="search">
            <label for="search">
                <span class="sr-only"><%t Pixelpoems\Search\Pages\SearchPage.Search 'Search' %></span>
                <button class="btn btn__search" aria-label="<%t Pixelpoems\Search\Pages\SearchPage.Search 'Search' %>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </label>
            <div class="search-bar">
                <input class="search-input" id="search" type="text" placeholder="<%t Pixelpoems\Search\Pages\SearchPage.Search 'Search' %>"/>
                <span class="search-loader hidden"></span>
                <button class="btn btn__close" aria-label="Close Search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        </div>
        <div class="search-result search-result__inline js-result-list hidden"></div>
    </div>
<% end_if %>




<div>
    <span class="search-result__count">
        <% if $IsInline %>
            <% if $List.Count < 10 %>
                <%t Pixelpoems\Search\Pages\SearchPage.Matches 'Matches' %>: $List.Count
            <% else %>
                <% if $IsInline && $SearchPageLink %>
                    <a href="{$SearchPageLink}" class="search-result__more"><%t Pixelpoems\Search\Pages\SearchPage.MoreResults 'See more' %></a>
                <% end_if %>
            <% end_if %>
        <% else %>
            <%t Pixelpoems\Search\Pages\SearchPage.Matches 'Matches' %>: $List.Count
        <% end_if %>
    </span>

    <% if $List && $List.Count > 0 %>
        <ul class="search-result__list">
            <% loop $List %>
                <li>
                    <a href="$Link">
                        <span class="search-result__headline">$Title</span>
                        <span class="search-result__content">$Content.LimitCharactersToClosestWord(120)</span>
                    </a>
                </li>
            <% end_loop %>
        </ul>

        <% if $IsInline && $SearchPageLink %>
            <a href="{$SearchPageLink}" class="search-result__more"><%t Pixelpoems\Search\Pages\SearchPage.MoreResults 'See more' %></a>
        <% end_if %>

    <% else %>
        <span class="search-result__noresult">
        <%t Pixelpoems\Search\Pages\SearchPage.NoResults 'No results' %>
        </span>
    <% end_if %>
</div>

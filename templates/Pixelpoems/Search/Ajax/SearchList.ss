<div>
    <p><%t Pixelpoems\Search\Pages\SearchPage.Matches 'Matches' %>: $List.Count</p>

    <% if $List && $List.Count > 0 %>
        <ul>
            <% loop $List %>
                <li>
                    <a href="$Link">$Title</a>
                </li>
            <% end_loop %>
        </ul>

        <% if $IsInline && $SearchPageLink %>
            <a href="{$SearchPageLink}" class="search-result__more"><%t Pixelpoems\Search\Pages\SearchPage.MoreResults 'See more' %></a>
        <% end_if %>

    <% else %>
        <p>
        <%t Pixelpoems\Search\Pages\SearchPage.NoResults 'No results' %>
        </p>
    <% end_if %>
</div>
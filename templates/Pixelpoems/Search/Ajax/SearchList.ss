<div>
    Matches: $List.Count
</div>

<% if $List && $List.Count > 0 %>
    <ul>
        <% loop $List %>
            <li>
                <a href="$Link">$Title</a>
            </li>
        <% end_loop %>
        <% if $IsInline && $SearchPageLink %>
            <li>
                <a href="{$SearchPageLink}" class="search-see-more">See more...</a>
            </li>
        <% end_if %>
    </ul>
<% else %>
    No search results
<% end_if %>

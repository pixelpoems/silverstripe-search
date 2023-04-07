<div>
    Matches: $List.Count
</div>

<% if $List %>
    <ul>
        <% loop $List %>
            <li>
                <a href="$Link">$Title</a>
            </li>
        <% end_loop %>
    </ul>
<% end_if %>

<div>
    Matches: $Pages.Count
</div>

<% if $Pages %>
    <ul>
        <% loop $Pages %>
            <li><a href="$Link">$Title</a></li>
        <% end_loop %>
    </ul>
<% end_if %>

<% $title = 'Foreach Tests' %>
<% include header %>

<ul>
<% foreach $i in $list %>
	<li><% $i %></li>
<% end %>
</ul>
<ul>
<% foreach $k,$i in $list %>
	<li><% $k %>=<% $i %></li>
<% end %>
</ul>

<% include footer %>

{{ section name='title' }}Sugar Template Test Cases{{ /section }}

<ul>
{{ foreach $tpl in $templates}}
	{{ if $tpl != 'index' && $tpl != 'header' && $tpl != 'footer' }}
		<li><a href="index.php?t={{$tpl}}">{{$tpl}}</a></li>
	{{ /if }}
{{ /foreach }}
</ul>

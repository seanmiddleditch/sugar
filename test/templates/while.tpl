{% $title = 'While Tests' %}
{% include tpl= 'header' %}

<p>Test: $i = 0; while $i < 10; $i ; $i = $i + 1; end<br/>
Expect: 0123456789<br/>
Result:
{% $i = 0; while $i < 10; $i ; $i = $i + 1; end %}</p>

<p>Test: $i = 1; while $i in [1, 2, 4, 8, 16, 32, 60, 128]; $i ; $i = $i * 2; end<br/>
Expect: 12481632<br/>
Result: {% $i = 1; while $i in [1, 2, 4, 8, 16, 32, 60, 128]; $i ; $i = $i * 2; end %}</p>

{% include tpl= 'footer' %}

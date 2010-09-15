{{ $list = ['one','two','three','bar'] }}

Test: foreach $list as $i; $i..','; /foreach
Expect: one,two,three,bar,
Result: {{ foreach $list as $i; $i..','; /foreach }}

Test: foreach $list as $k=>$i; $k .. '=' .. $i .. ','; /foreach
Expect: 0=one,1=two,2=three,3=bar,
Result: {{ foreach $list as $k=>$i; $k .. '=' .. $i .. ','; /foreach }}

Test: foreach [1, 'one', 'bar', 42] as $i; $i..','; /foreach
Expect: 1,one,bar,42,
Result: {{ foreach [1, 'one', 'bar', 42] as $i; $i..','; /foreach }}

--TEST--
Check if multiple hooks are invoked
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(null, 'helloWorld', fn() => var_dump('PRE_1'), fn() => var_dump('POST_1'));
\Phook\hook(null, 'helloWorld', fn() => var_dump('PRE_2'), fn() => var_dump('POST_2'));

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECT--
string(5) "PRE_1"
string(5) "PRE_2"
string(4) "CALL"
string(6) "POST_2"
string(6) "POST_1"

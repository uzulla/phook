--TEST--
Check if pre hook can modify not provided arguments
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(null, 'helloWorld', fn() => [1 => 'b']);

function helloWorld($a = null, $b = null) {
    var_dump($a, $b);
}

helloWorld();
?>
--EXPECT--
NULL
string(1) "b"

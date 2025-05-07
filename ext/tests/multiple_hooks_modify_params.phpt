--TEST--
Check if hooks receive modified arguments
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(null, 'helloWorld', fn(mixed $object, array $params) => [++$params[0]]);
\Phook\hook(null, 'helloWorld', fn(mixed $object, array $params) => [++$params[0]]);

function helloWorld($a) {
    var_dump($a);
}

helloWorld('a');
?>
--EXPECT--
string(1) "c"

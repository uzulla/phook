--TEST--
Check if hooks receive modified return value
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(null, 'helloWorld', post: fn(mixed $object, array $params, int $return): int => ++$return);
\Phook\hook(null, 'helloWorld', post: fn(mixed $object, array $params, int $return): int => ++$return);

function helloWorld(int $val): int {
    return $val;
}

var_dump(helloWorld(1));
?>
--EXPECT--
int(3)

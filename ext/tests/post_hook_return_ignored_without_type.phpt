--TEST--
Check if post hook return value is ignored if return typehint not supplied
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return) => 'ignored');

function helloWorld(string $val) {
    return $val;
}

var_dump(helloWorld('foo'));
?>
--EXPECT--
string(3) "foo"

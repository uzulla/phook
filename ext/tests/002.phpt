--TEST--
Check if hook returns true
--EXTENSIONS--
phook
--FILE--
<?php
$ret = \Phook\hook(null, 'some_function');

var_dump($ret);
?>
--EXPECT--
bool(true)

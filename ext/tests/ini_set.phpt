--TEST--
Check if process exits gracefully after using ini_set with a phook option
--EXTENSIONS--
phook
--FILE--
<?php
ini_set('phook.conflicts', 'test');
var_dump('done');
?>
--EXPECT--
string(4) "done"

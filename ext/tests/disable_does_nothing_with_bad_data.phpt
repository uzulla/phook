--TEST--
Check if phook disable ignores bad input
--EXTENSIONS--
phook
--INI--
phook.conflicts=,
--FILE--
<?php
var_dump(extension_loaded('phook'));
?>
--EXPECTF--
bool(true)

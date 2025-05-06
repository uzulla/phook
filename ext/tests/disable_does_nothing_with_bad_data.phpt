--TEST--
Check if opentelemetry disable ignores bad input
--EXTENSIONS--
phook
--INI--
phook.conflicts=,
--FILE--
<?php
var_dump(extension_loaded('opentelemetry'));
?>
--EXPECTF--
bool(true)

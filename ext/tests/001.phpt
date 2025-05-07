--TEST--
Check if phook extension is loaded
--EXTENSIONS--
phook
--FILE--
<?php
printf('The extension "phook" is available, version %s', phpversion('phook'));
?>
--EXPECTF--
The extension "phook" is available, version %s

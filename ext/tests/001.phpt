--TEST--
Check if phook extension is loaded
--SKIPIF--
<?php
if (!extension_loaded('phook')) echo 'skip phook extension not loaded';
?>
--FILE--
<?php
ob_start();
printf('The extension "phook" is available, version %s', phpversion('phook'));
$output = ob_get_clean();
echo $output;
?>
--EXPECTF--
The extension "phook" is available, version %s

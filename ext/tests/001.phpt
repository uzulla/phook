--TEST--
Check if phook extension is loaded
--EXTENSIONS--
phook
--FILE--
<?php
ob_start();
printf('The extension "phook" is available, version %s', phpversion('phook'));
$output = ob_get_clean();
echo $output;
?>
--EXPECTF--
The extension "phook" is available, version %s

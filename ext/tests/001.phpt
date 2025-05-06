--TEST--
Check if opentelemetry extension is loaded
--EXTENSIONS--
phook
--FILE--
<?php
printf('The extension "opentelemetry" is available, version %s', phpversion('opentelemetry'));
?>
--EXPECTF--
The extension "opentelemetry" is available, version %s

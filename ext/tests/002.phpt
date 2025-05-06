--TEST--
Check if hook returns true
--EXTENSIONS--
phook
--FILE--
<?php
$ret = \OpenTelemetry\Instrumentation\hook(null, 'some_function');

var_dump($ret);
?>
--EXPECT--
bool(true)

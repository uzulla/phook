--TEST--
Check if pre hook can replace arguments
--EXTENSIONS--
phook
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => ['b']);

function helloWorld($a) {
    var_dump($a);
}

helloWorld('a');
?>
--EXPECT--
string(1) "b"

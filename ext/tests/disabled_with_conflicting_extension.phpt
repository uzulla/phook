--TEST--
Check if opentelemetry extension is loaded but disabled with a conflicting extension
--EXTENSIONS--
phook
--INI--
phook.conflicts=Core
--FILE--
<?php
?>
--EXPECTF--
Notice: PHP Startup: Conflicting extension found (Core), OpenTelemetry extension will be disabled in %s

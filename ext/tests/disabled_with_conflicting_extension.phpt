--TEST--
Check if phook extension is loaded but disabled with a conflicting extension
--EXTENSIONS--
phook
--INI--
phook.conflicts=Core
--FILE--
<?php
?>
--EXPECTF--
Notice: PHP Startup: Conflicting extension found (Core), Phook extension will be disabled in %s

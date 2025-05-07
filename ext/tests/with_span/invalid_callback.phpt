--TEST--
Invalid callback is ignored
--EXTENSIONS--
phook
--INI--
phook.attr_hooks_enabled = On
phook.attr_pre_handler_function = "Invalid::pre"
phook.attr_post_handler_function = "Also\Invalid::post"
--FILE--
<?php
use Phook\WithSpan;

#[WithSpan]
function foo(): void
{
  var_dump('test');
}

var_dump(ini_get('phook.attr_pre_handler_function'));
var_dump(ini_get('phook.attr_post_handler_function'));
foo();
?>
--EXPECT--
string(12) "Invalid::pre"
string(18) "Also\Invalid::post"
string(4) "test"

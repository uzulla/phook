--TEST--
Check if WithSpan handlers can be changed via config
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
phook
--INI--
phook.attr_hooks_enabled = On
phook.attr_pre_handler_function = custom_pre
phook.attr_post_handler_function = custom_post
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
use Phook\WithSpan;

function custom_pre(): void
{
    var_dump('custom_pre handler');
}

function custom_post(): void
{
    var_dump('custom_post handler');
}

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
string(10) "custom_pre"
string(11) "custom_post"
string(18) "custom_pre handler"
string(4) "test"
string(19) "custom_post handler"
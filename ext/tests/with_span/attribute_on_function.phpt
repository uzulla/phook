--TEST--
Check if custom attribute loaded
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
phook
--INI--
phook.attr_hooks_enabled = On
--FILE--
<?php
namespace Phook;

include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/WithSpanHandler.php';
use Phook\WithSpan;

#[WithSpan]
function phook_attr_test(): void
{
  var_dump('test');
}

$reflection = new \ReflectionFunction('Phook\phook_attr_test');
var_dump($reflection->getAttributes()[0]->getName() == WithSpan::class);

phook_attr_test();
?>
--EXPECT--
bool(true)
string(3) "pre"
string(4) "test"
string(4) "post"

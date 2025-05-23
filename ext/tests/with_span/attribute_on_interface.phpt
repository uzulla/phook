--TEST--
Check if custom attribute can be applied to an interface
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

interface TestInterface
{
    #[WithSpan]
    public function sayFoo(): void;
}

interface OtherInterface
{
    #[WithSpan]
    public function sayBar(): void;
}

class TestClass implements TestInterface, OtherInterface
{
    public function sayFoo(): void
    {
        var_dump('foo');
    }
    public function sayBar(): void
    {
        var_dump('bar');
    }
}

$c = new TestClass();
$c->sayFoo();
$c->sayBar();
?>
--EXPECT--
string(3) "pre"
string(3) "foo"
string(4) "post"
string(3) "pre"
string(3) "bar"
string(4) "post"
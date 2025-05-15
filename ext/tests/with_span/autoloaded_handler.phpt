--TEST--
Check if WithSpanHandler can be provided by an autoloader
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
phook
--INI--
phook.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';

use Phook\WithSpan;
use Phook\WithSpanHandler;

function my_autoloader($class) {
    var_dump("autoloading: " . $class);
    $file = dirname(__DIR__) . '/mocks/WithSpanHandler.php';
    if (file_exists($file)) {
        include $file;
    } else {
        die("could not autoload: ".$class);
    }
}
spl_autoload_register('my_autoloader');

class TestClass
{
    #[WithSpan]
    function sayFoo(): void
    {
        var_dump('foo');
    }
}

$c = new TestClass();
$c->sayFoo();
?>
--EXPECTF--
string(32) "autoloading: Phook\WithSpanHandl"
string(32) "autoloading: Phook\WithSpanHandl"

Fatal error: Cannot declare class Phook\WithSpanHandler, because the name is already in use in %s/tests/mocks/WithSpanHandler.php on line %d

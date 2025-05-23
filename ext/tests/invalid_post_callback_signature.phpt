--TEST--
Test invalid post callback signature
--DESCRIPTION--
The invalid callback signature should not cause a fatal, so it is checked before execution. If the function signature
is invalid, the callback will not be called.
--EXTENSIONS--
phook
--FILE--
<?php
Phook\hook(
    'TestClass',
    'test',
    static function () {
        var_dump('pre');
    },
    static function (array $params) {
        //missing param 1 (object)
        var_dump('post');
    }
);

class TestClass {
    public static function test(): void
    {
        var_dump('test');
    }
}

TestClass::test();
?>
--EXPECTF--
string(3) "pre"
string(4) "test"

Warning: TestClass::test(): Phook: post hook invalid signature, class=TestClass function=test in %s on line %d

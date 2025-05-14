<?php
Phook\hook(
    'TestClass',
    'test',
    static function (array $params, string $class, string $function, ?string $filename, ?int $lineno) {
        var_dump('pre');
    },
    static function () {
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

--TEST--
Test invalid pre callback signature for function without class
--DESCRIPTION--
The invalid callback signature should not cause a fatal, so it is checked before execution. If the function signature
is invalid, the callback will not be called and a message will be written to error_log. Also tests logging handling of
null class.
--EXTENSIONS--
phook
--FILE--
<?php
Phook\hook(
    null,
    'hello',
    static function (array $params, string $class, string $function, ?string $filename, ?int $lineno) {
        //missing param 1 (object)
        var_dump('pre');
    },
    static function () {
        var_dump('post');
    }
);

function hello(): void
{
    var_dump('hello');
}

hello();
?>
--EXPECTF--
Warning: hello(): Phook: pre hook invalid signature, class=null function=hello in %s on line %d
string(5) "hello"
string(4) "post"

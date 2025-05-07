--TEST--
Check if post hook receives exception
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(
    null,
    'helloWorld',
    null,
    fn(object|null $obj, array $params, mixed $returnValue, ?Throwable $throwable) => var_dump($throwable?->getMessage()));

function helloWorld() {
    throw new Exception('error');
}

try {
    helloWorld();
} catch (Exception $e) {}
?>
--EXPECT--
string(5) "error"

--TEST--
Check if exceptions thrown in hooks interfere with internal exceptions
--EXTENSIONS--
phook
--FILE--
<?php
function helloWorld($argument) {
    var_dump('inside');
}
\Phook\hook(
    null,
    'helloWorld',
    pre: static function () : void {
        throw new \Exception('pre');
    },
    post: static function () : void {
        throw new \Exception('post');
    }
);
helloWorld();
?>
--EXPECTF--

Warning: helloWorld(): Phook: pre hook threw exception, class=null function=helloWorld message=pre in %s

Warning: helloWorld(): Phook: post hook threw exception, class=null function=helloWorld message=post in %s

Fatal error: Uncaught ArgumentCountError: Too few arguments to function helloWorld(), 0 passed in %s
Stack trace:
#0 %s: helloWorld()
#1 {main}
  thrown in %s

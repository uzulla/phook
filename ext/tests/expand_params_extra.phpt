--TEST--
Check if pre hook can expand params of function with extra parameters not provided by call site
--DESCRIPTION--
Extra parameters for user functions are parameters that were provided at call site but were not present in the function
declaration. The extension only supports modifying existing ones, not adding new ones. Test that a warning is logged if
adding new ones is attempted and that it does not crash.
--EXTENSIONS--
phook
--FILE--
<?php
Phook\hook(
    null,
    'helloWorld',
    pre: function($instance, array $params) {
        return [$params[0], 'b', 'c', 'd'];
    },
    post: fn() => null
);

function helloWorld($a, $b) {
    var_dump(func_get_args());
}
helloWorld('a');
?>
--EXPECTF--
Warning: helloWorld(): Phook: pre hook invalid argument index 2 - stack extension must be enabled with phook.allow_stack_extension option, class=null function=helloWorld in %s
array(2) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
}

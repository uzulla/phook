--TEST--
Check if pre hook can expand and then return $params of internal function
--DESCRIPTION--
The existence of a post callback is part of the failure preconditions.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
--EXTENSIONS--
phook
--FILE--
<?php
\Phook\hook(
    null,
    'array_slice',
    pre: function($obj, array $params) {
        $params[2] = 1; //only slice 1 value, instead of "remainder"
        return $params;
    },
    post: fn() => null //does not fail without post callback
);

var_dump(array_slice(['a', 'b', 'c'], 1));
?>
--EXPECTF--
Warning: array_slice(): Phook: pre hook invalid argument index 2 - stack extension must be enabled with phook.allow_stack_extension option, class=null function=array_slice in %s
array(2) {
  [0]=>
  string(1) "b"
  [1]=>
  string(1) "c"
}

<?php

namespace Phook;

class WithSpanHandler
{
    public static function pre(): void
    {
        var_dump('pre');
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

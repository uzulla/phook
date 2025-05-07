<?php

namespace Phook;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SpanAttribute
{
    public function __construct(
        public readonly ?string $name = null,
    ) {
    }
}

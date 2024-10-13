<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\StringSubsetType;

interface StringValue extends LiteralValue {
    public function type(): StringSubsetType;
    public function literalValue(): string;
}
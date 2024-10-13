<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\RealSubsetType;

interface RealValue extends LiteralValue {
    public function type(): RealSubsetType;
    public function literalValue(): float;
}
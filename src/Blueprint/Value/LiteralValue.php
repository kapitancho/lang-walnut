<?php

namespace Walnut\Lang\Blueprint\Value;

interface LiteralValue extends Value {
    public function literalValue(): int|float|string|bool|null;
}
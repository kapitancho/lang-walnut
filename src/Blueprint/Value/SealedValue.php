<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\SealedType;

interface SealedValue extends Value {
    public function type(): SealedType;
    public function value(): RecordValue;
}
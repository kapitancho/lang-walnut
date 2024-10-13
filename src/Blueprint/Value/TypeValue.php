<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;

interface TypeValue extends Value {
    public function type(): TypeType;
    public function typeValue(): Type;
}
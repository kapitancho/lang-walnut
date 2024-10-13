<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\NullValue;

interface NullType extends AtomType {
    public function value(): NullValue;
}
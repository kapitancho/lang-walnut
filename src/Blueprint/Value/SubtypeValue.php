<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\SubtypeType;

interface SubtypeValue extends Value {
    public function type(): SubtypeType;
    public function baseValue(): Value;
}
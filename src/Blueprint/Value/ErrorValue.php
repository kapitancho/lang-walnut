<?php

namespace Walnut\Lang\Blueprint\Value;

use Walnut\Lang\Blueprint\Type\ResultType;

interface ErrorValue extends Value {
    public function type(): ResultType;
    public function errorValue(): Value;
}
<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Value\AtomValue;

interface AtomType extends NamedType {
    public function value(): AtomValue;
}
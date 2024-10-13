<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;

interface NamedType extends Type {
    public function name(): TypeNameIdentifier;
}
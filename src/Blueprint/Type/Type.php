<?php

namespace Walnut\Lang\Blueprint\Type;

use Stringable;

interface Type extends Stringable {
    public function isSubtypeOf(Type $ofType): bool;
}
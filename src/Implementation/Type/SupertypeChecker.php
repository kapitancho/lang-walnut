<?php

namespace Walnut\Lang\Implementation\Type;

use Walnut\Lang\Blueprint\Type\Type;

interface SupertypeChecker {
    public function isSupertypeOf(Type $ofType): bool;
}
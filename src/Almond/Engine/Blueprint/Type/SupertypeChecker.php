<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Type;

interface SupertypeChecker {
    public function isSupertypeOf(Type $ofType): bool;
}
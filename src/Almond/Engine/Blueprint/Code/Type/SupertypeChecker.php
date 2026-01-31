<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Type;

interface SupertypeChecker {
    public function isSupertypeOf(Type $ofType): bool;
}
<?php

namespace Walnut\Lang\Blueprint\Type;

interface SupertypeChecker {
    public function isSupertypeOf(Type $ofType): bool;
}
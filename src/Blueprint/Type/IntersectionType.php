<?php

namespace Walnut\Lang\Blueprint\Type;

interface IntersectionType extends Type {
    /** @return non-empty-list<Type> */
    public function types(): array;
}
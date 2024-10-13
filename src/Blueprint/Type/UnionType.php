<?php

namespace Walnut\Lang\Blueprint\Type;

interface UnionType extends Type {
    /** @return non-empty-list<Type> */
    public function types(): array;
}
<?php

namespace Walnut\Lang\Blueprint\Type;

interface AliasType extends NamedType {
    public function aliasedType(): Type;
}
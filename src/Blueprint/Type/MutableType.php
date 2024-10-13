<?php

namespace Walnut\Lang\Blueprint\Type;

interface MutableType extends Type {
    public function valueType(): Type;
}
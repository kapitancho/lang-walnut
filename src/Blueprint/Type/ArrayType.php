<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\LengthRange;

interface ArrayType extends Type {
    public function itemType(): Type;
    public function range(): LengthRange;
}
<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Range\LengthRange;

interface MapType extends Type {
    public function itemType(): Type;
    public function range(): LengthRange;
}
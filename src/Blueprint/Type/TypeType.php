<?php

namespace Walnut\Lang\Blueprint\Type;

interface TypeType extends Type {
    public function refType(): Type;
}
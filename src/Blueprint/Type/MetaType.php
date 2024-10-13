<?php

namespace Walnut\Lang\Blueprint\Type;

interface MetaType extends Type {
    public function value(): MetaTypeValue;
}
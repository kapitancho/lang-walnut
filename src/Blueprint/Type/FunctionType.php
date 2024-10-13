<?php

namespace Walnut\Lang\Blueprint\Type;

interface FunctionType extends Type {
    public function parameterType(): Type;
    public function returnType(): Type;
}
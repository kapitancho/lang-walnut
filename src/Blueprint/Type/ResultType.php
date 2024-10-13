<?php

namespace Walnut\Lang\Blueprint\Type;

interface ResultType extends Type {
    public function returnType(): Type;
    public function errorType(): Type;
}
<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;
use Walnut\Lang\Blueprint\Value\Value;

interface MethodFinder extends MethodRegistry {
	public function methodForValue(Value $target, MethodNameIdentifier $methodName): Method|UnknownMethod;
}
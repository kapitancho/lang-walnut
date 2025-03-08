<?php

namespace Walnut\Lang\Blueprint\Program\Registry;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\UnknownMethod;

interface MethodFinder extends MethodRegistry {
	public function methodForValue(TypedValue $target, MethodNameIdentifier $methodName): Method|UnknownMethod;
}
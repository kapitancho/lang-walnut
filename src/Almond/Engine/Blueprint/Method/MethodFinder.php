<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface MethodFinder {
	public function methodForType(Type $targetType, MethodName $methodName): Method|UnknownMethod;
	public function methodForValue(Value $target, MethodName $methodName): Method|UnknownMethod;
}
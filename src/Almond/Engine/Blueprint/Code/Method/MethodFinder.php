<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

interface MethodFinder {
	public function methodForType(Type $targetType, MethodName $methodName): Method|UnknownMethod;
	public function methodForValue(Value $target, MethodName $methodName): Method|UnknownMethod;
}
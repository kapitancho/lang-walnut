<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Registry;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

interface MethodRegistry {
	public function methodFor(Type $type, MethodName $methodName): Method|UnknownMethod;
}
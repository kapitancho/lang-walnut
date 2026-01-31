<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

interface MethodRegistry {
	public function methodFor(Type $type, MethodName $methodName): Method|UnknownMethod;
}
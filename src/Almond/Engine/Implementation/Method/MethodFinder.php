<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodFinder as MethodFinderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\MethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

final readonly class MethodFinder implements MethodFinderInterface {

	public function __construct(
		private MethodRegistry $methodRegistry
	) {}

	public function methodForType(Type $targetType, MethodName $methodName): Method|UnknownMethod {
		return $this->methodRegistry->methodFor($targetType, $methodName);
	}

	public function methodForValue(Value $target, MethodName $methodName): Method|UnknownMethod {
		return $this->methodForType($target->type, $methodName);
	}
}
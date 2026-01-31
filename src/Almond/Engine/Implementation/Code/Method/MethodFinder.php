<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Method;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodFinder as MethodFinderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;

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
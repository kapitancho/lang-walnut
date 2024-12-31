<?php

namespace Walnut\Lang\NativeCode\Null;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsInteger implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		return $this->context->typeRegistry->integerSubset([
			$this->context->valueRegistry->integer(0)
		]);
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof NullValue) {
			return TypedValue::forValue($this->context->valueRegistry->integer(0));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
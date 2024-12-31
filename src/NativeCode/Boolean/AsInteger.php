<?php

namespace Walnut\Lang\NativeCode\Boolean;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
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
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BooleanType) {
			return $this->context->typeRegistry->integerSubset([
				$this->context->valueRegistry->integer(0),
				$this->context->valueRegistry->integer(1)
			]);
		}
		if ($targetType instanceof TrueType) {
			return $this->context->typeRegistry->integerSubset([
				$this->context->valueRegistry->integer(1)
			]);
		}
		if ($targetType instanceof FalseType) {
			return $this->context->typeRegistry->integerSubset([
				$this->context->valueRegistry->integer(0)
			]);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof BooleanValue) {
			$target = $targetValue->literalValue;
			return TypedValue::forValue($this->context->valueRegistry->integer($target ? 1 : 0));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
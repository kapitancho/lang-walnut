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

final readonly class BinaryXor implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BooleanType || $targetType instanceof TrueType || $targetType instanceof FalseType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof BooleanType || $parameterType instanceof TrueType || $parameterType instanceof FalseType) {
				return match(true) {
					($targetType instanceof FalseType && $parameterType instanceof FalseType) ||
					($targetType instanceof TrueType && $parameterType instanceof TrueType) => $this->context->typeRegistry()->false(),
					($targetType instanceof FalseType && $parameterType instanceof TrueType) ||
					($targetType instanceof TrueType && $parameterType instanceof FalseType) => $this->context->typeRegistry()->true(),
					default => $this->context->typeRegistry()->boolean()
				};
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
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
		$parameterValue = $parameter->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof BooleanValue) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof BooleanValue) {
	            return TypedValue::forValue($this->context->valueRegistry()->boolean(
		            ($targetValue->literalValue() && !$parameterValue->literalValue()) ||
		            (!$targetValue->literalValue() && $parameterValue->literalValue())
	            ));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
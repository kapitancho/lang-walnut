<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class UpTo implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof IntegerSubsetType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType) {
				$maxLength = max(0,
					$parameterType->range()->maxValue() === PlusInfinity::value ||
					$targetType->range()->minValue() === MinusInfinity::value ? PlusInfinity::value :
					1 + $parameterType->range()->maxValue() - $targetType->range()->minValue(),
				);
				return $this->context->typeRegistry()->array(
					$maxLength > 0 ? $this->context->typeRegistry()->integer(
						$targetType->range()->minValue(),
						$parameterType->range()->maxValue()
					) : $this->context->typeRegistry()->nothing(),
					max(0,
						$targetType->range()->maxValue() === PlusInfinity::value ||
						$parameterType->range()->minValue() === MinusInfinity::value ? 0 :
							1 + $parameterType->range()->minValue() - $targetType->range()->maxValue()
					),
					$maxLength
				);
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
		if ($targetValue instanceof IntegerValue) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof IntegerValue) {
	            return TypedValue::forValue($this->context->valueRegistry()->tuple(
					$targetValue->literalValue() < $parameterValue->literalValue()  ?
						array_map(fn(int $i): IntegerValue =>
							$this->context->valueRegistry()->integer($i),
							range($targetValue->literalValue(), $parameterValue->literalValue())
						) : []
	            ));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
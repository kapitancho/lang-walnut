<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryDivide implements NativeMethod {
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

			if ($parameterType instanceof IntegerType ||
				$parameterType instanceof IntegerSubsetType ||
				$parameterType instanceof RealType ||
				$parameterType instanceof RealSubsetType
			) {
                $real = $this->context->typeRegistry->real();
                if (
                    $targetType->range->minValue >= 0 && $parameterType->range->minValue > 0
                ) {
                    $min = $parameterType->range->maxValue === PlusInfinity::value ? 0 :
                        $targetType->range->minValue / $parameterType->range->maxValue;
                    $max = $targetType->range->maxValue === PlusInfinity::value ? PlusInfinity::value :
                        $targetType->range->maxValue / $parameterType->range->minValue;
                    $real = $this->context->typeRegistry->real($min, $max);
                }
				return ($parameterType->range->minValue === MinusInfinity::value || $parameterType->range->minValue < 0) &&
					($parameterType->range->maxValue === PlusInfinity::value || $parameterType->range->maxValue > 0) ?
						$this->context->typeRegistry->result(
							$real,
							$this->context->typeRegistry->atom(new TypeNameIdentifier('NotANumber'))
						) : $real;
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
		$parameterValue = $this->toBaseValue($parameterValue);

		if ($targetValue instanceof RealValue || $targetValue instanceof IntegerValue) {
			$parameterValue = $this->toBaseValue($parameterValue);
			if ($parameterValue instanceof IntegerValue || $parameterValue instanceof RealValue) {
				if ((float)(string)$parameterValue->literalValue === 0.0) {
					return TypedValue::forValue($this->context->valueRegistry->error(
						$this->context->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					));
				}
                return TypedValue::forValue($this->context->valueRegistry->real(
	                fdiv((string)$targetValue->literalValue, (string)$parameterValue->literalValue)
	                //$targetValue->literalValue / $parameter->literalValue
                ));
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
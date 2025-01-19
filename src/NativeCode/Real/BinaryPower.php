<?php

namespace Walnut\Lang\NativeCode\Real;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\IntegerValue;

final readonly class BinaryPower implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RealType || $targetType instanceof RealSubsetType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType ||
				$parameterType instanceof IntegerSubsetType ||
				$parameterType instanceof RealType ||
				$parameterType instanceof RealSubsetType
			) {
				return $programRegistry->typeRegistry->real();
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
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
                return TypedValue::forValue($programRegistry->valueRegistry->real(
					str_contains($parameterValue->literalValue, '.') ?
						((float)(string)$targetValue->literalValue) ** ((float)(string)$parameterValue->literalValue) :
	                    $targetValue->literalValue->pow($parameterValue->literalValue)
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
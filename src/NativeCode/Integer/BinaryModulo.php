<?php

namespace Walnut\Lang\NativeCode\Integer;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof IntegerSubsetType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType ||
				$parameterType instanceof RealType || $parameterType instanceof RealSubsetType
			) {
				$includesZero =
					($parameterType->range->minValue === MinusInfinity::value || $parameterType->range->minValue < 0) &&
					($parameterType->range->maxValue === PlusInfinity::value || $parameterType->range->maxValue > 0);
				$returnType = $parameterType instanceof IntegerType || $parameterType instanceof IntegerSubsetType ?
					$programRegistry->typeRegistry->integer() : $programRegistry->typeRegistry->real();

				return $includesZero ? $programRegistry->typeRegistry->result(
					$returnType,
					$programRegistry->typeRegistry->atom(new TypeNameIdentifier('NotANumber'))
				) : $returnType;
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
		
				if ($targetValue instanceof IntegerValue) {
			if ($parameterValue instanceof IntegerValue) {
				if ((int)(string)$parameterValue->literalValue === 0) {
					return TypedValue::forValue($programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					));
				}
                return TypedValue::forValue($programRegistry->valueRegistry->integer(
	                $targetValue->literalValue % $parameterValue->literalValue
                ));
			}
			if ($parameterValue instanceof RealValue) {
				if ((float)(string)$parameterValue->literalValue === 0.0) {
					return TypedValue::forValue($programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(new TypeNameIdentifier('NotANumber'))
					));
				}
                return TypedValue::forValue($programRegistry->valueRegistry->real(
	                fmod((string)$targetValue->literalValue, (string)$parameterValue->literalValue)
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
<?php

namespace Walnut\Lang\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\LengthRange;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Digits implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$minValue = $targetType->numberRange->min;
			$maxValue = $targetType->numberRange->max;
			if ($minValue !== MinusInfinity::value && $minValue->value >= '0') {
				if ($this->toBaseType($parameterType) instanceof NullType) {
					$minLength = $this->digitCount($minValue->value);
					$maxLength = $maxValue === PlusInfinity::value ? null : $this->digitCount($maxValue->value);

					return $typeRegistry->array(
						$targetType,
						$minLength,
						$maxLength ?? PlusInfinity::value
					);
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function digitCount($bound): Number|PlusInfinity {
		if ($bound === PlusInfinity::value) {
			return PlusInfinity::value;
		}

		$value = (string)$bound->value;
		if ($value === '0') {
			return new Number(1);
		}

		// For negative numbers, we'll handle in execution
		if ($value[0] === '-') {
			return new Number(strlen($value) - 1); // Exclude the minus sign
		}

		return new Number(strlen($value));
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof IntegerValue) {
			$value = (int)(string)$target->literalValue;

			// Check for negative values
			if ($value >= 0) {
				// Convert to string and split into digits
				$valueStr = (string)$value;
				$digits = [];

				for ($i = 0; $i < strlen($valueStr); $i++) {
					$digits[] = $programRegistry->valueRegistry->integer((int)$valueStr[$i]);
				}

				return $programRegistry->valueRegistry->tuple($digits);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}

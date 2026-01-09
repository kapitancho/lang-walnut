<?php

namespace Walnut\Lang\NativeCode\Real;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Sign implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType || $targetType instanceof RealType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof NullType) {
				$min = $targetType->numberRange->min;
				$max = $targetType->numberRange->max;

				$values = [];
				if ($min === MinusInfinity::value || (string)$min->value < 0) {
					$values[] = new Number(-1);
				}
				if (($min === MinusInfinity::value || (string)$min->value <= 0) && ($max === PlusInfinity::value || (string)$max->value >= 0)) {
					$values[] = new Number(0);
				}
				if ($max === PlusInfinity::value || (string)$max->value > 0) {
					$values[] = new Number(1);
				}
				return $typeRegistry->integerSubset($values);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof RealValue || $target instanceof IntegerValue) {
			$val = (string)$target->literalValue;
			if ($val > 0) {
				return $programRegistry->valueRegistry->integer(1);
			} elseif ($val < 0) {
				return $programRegistry->valueRegistry->integer(-1);
			} else {
				return $programRegistry->valueRegistry->integer(0);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}

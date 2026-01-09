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
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Abs implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RealType) {
			return $typeRegistry->realFull(
				new NumberInterval(
					match(true) {
						$targetType->numberRange->max !== PlusInfinity::value && $targetType->numberRange->max->value < 0 =>
							new NumberIntervalEndpoint(
								new Number((string)abs((float)(string)$targetType->numberRange->max->value)),
								$targetType->numberRange->max->inclusive
							),
						$targetType->numberRange->min !== MinusInfinity::value && $targetType->numberRange->min->value >= 0 =>
							$targetType->numberRange->min,
						default => new NumberIntervalEndpoint(new Number(0), true)
					},
					$targetType->numberRange->min === MinusInfinity::value || $targetType->numberRange->max === PlusInfinity::value ?
						PlusInfinity::value :
						new NumberIntervalEndpoint(
							new Number(
								(string)max(
									abs((float)(string)$targetType->numberRange->min->value),
									abs((float)(string)$targetType->numberRange->max->value)
								)
							),
							abs((float)(string)$targetType->numberRange->min->value) >
							abs((float)(string)$targetType->numberRange->max->value) ?
								$targetType->numberRange->min->inclusive :
								$targetType->numberRange->max->inclusive
						)
				)
			);
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
			return $programRegistry->valueRegistry->real(abs((float)(string)$target->literalValue));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
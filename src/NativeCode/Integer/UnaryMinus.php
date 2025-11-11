<?php

namespace Walnut\Lang\NativeCode\Integer;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\NumberInterval as NumberIntervalInterface;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class UnaryMinus implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerSubsetType) {
			return $typeRegistry->integerSubset(
				array_map(fn(Number $value): Number =>
					$value->mul(-1),
					$targetType->subsetValues
				)
			);
		}
		if ($targetType instanceof IntegerType) {
			return $typeRegistry->integerFull(...
				array_map(
					fn(NumberIntervalInterface $interval): NumberIntervalInterface =>
						new NumberInterval(
							$interval->end instanceof PlusInfinity ?
								MinusInfinity::value :
								new NumberIntervalEndpoint(
									$interval->end->value->mul(-1),
									$interval->end->inclusive
								),
							$interval->start instanceof MinusInfinity ?
								PlusInfinity::value :
								new NumberIntervalEndpoint(
									$interval->start->value->mul(-1),
									$interval->start->inclusive
								)
						),
					$targetType->numberRange->intervals
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
		if ($target instanceof IntegerValue) {
			return $programRegistry->valueRegistry->integer(-$target->literalValue);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
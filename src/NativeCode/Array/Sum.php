<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Common\Range\NumberInterval;
use Walnut\Lang\Implementation\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Sum implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf(
				$typeRegistry->union([
					$typeRegistry->integer(),
					$typeRegistry->real()
				])
			)) {
				if ($itemType instanceof RealType) {
					return $typeRegistry->realFull(
						new NumberInterval(
							$itemType->numberRange->min === MinusInfinity::value ? MinusInfinity::value :
								new NumberIntervalEndpoint(
									$itemType->numberRange->min->value->mul($targetType->range->minLength),
									$itemType->numberRange->min->inclusive ||
									(int)(string)$targetType->range->minLength === 0
								),
							$itemType->numberRange->max === PlusInfinity::value ||
							$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
								new NumberIntervalEndpoint(
									$itemType->numberRange->max->value->mul($targetType->range->maxLength),
									$itemType->numberRange->max->inclusive
								)
						)
					);
				}
				if ($itemType instanceof IntegerType) {
					return $typeRegistry->integerFull(
						new NumberInterval(
							$itemType->numberRange->min === MinusInfinity::value ? MinusInfinity::value :
								new NumberIntervalEndpoint(
									$itemType->numberRange->min->value->mul($targetType->range->minLength),
									$itemType->numberRange->min->inclusive ||
									(int)(string)$targetType->range->minLength === 0
								),
								$itemType->numberRange->max === PlusInfinity::value ||
								$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
								new NumberIntervalEndpoint(
									$itemType->numberRange->max->value->mul($targetType->range->maxLength),
									$itemType->numberRange->max->inclusive
								)
						)
					);
				}
				return $typeRegistry->real();
			}
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
		if ($target instanceof TupleValue) {
			$sum = 0;
			$hasReal = false;
			foreach($target->values as $item) {
				$v = $item->literalValue;
				if (str_contains((string)$v, '.')) {
					$hasReal = true;
				}
				$sum += $v;
			}
			return $hasReal ? $programRegistry->valueRegistry->real($sum) : $programRegistry->valueRegistry->integer($sum);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
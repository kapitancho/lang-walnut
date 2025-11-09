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
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class SliceRange implements NativeMethod {
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
			$pInt = $typeRegistry->integer(0);
			$pType = $typeRegistry->record([
				"start" => $pInt,
				"end" => $pInt
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				$endType = $parameterType->types['end'];
				return $typeRegistry->array(
					$targetType->itemType,
					0,
					$endType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
						min(
							$targetType->range->maxLength,
							$parameterType->types['start']->numberRange->min === MinusInfinity::value ? // not possible
								// @codeCoverageIgnoreStart
								$targetType->range->maxLength :
								// @codeCoverageIgnoreEnd
								$endType->numberRange->max->value - $parameterType->types['start']->numberRange->min->value
						)
				);
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
		$targetValue = $target;
		$parameterValue = $parameter;
		
		if ($targetValue instanceof TupleValue) {
			if ($parameterValue instanceof RecordValue) {
				$start = $parameterValue->valueOf('start');
				$end = $parameterValue->valueOf('end');
				if (
					$start instanceof IntegerValue &&
					$end instanceof IntegerValue
				) {
					$length = $end->literalValue - $start->literalValue;
					$values = $targetValue->values;
					$values = array_slice(
						$values,
						(string)$start->literalValue,
						(string)$length
					);
					return $programRegistry->valueRegistry->tuple($values);
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
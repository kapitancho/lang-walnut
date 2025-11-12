<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array;

use BcMath\Number;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ArrayMinMax {
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
		if ($targetType instanceof ArrayType && $targetType->range->minLength > 0) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf(
				$typeRegistry->union([$typeRegistry->integer(), $typeRegistry->real()])
			)) {
				if ($parameterType instanceof NullType) {
					return $itemType;
				}
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @param callable(Number, Number): bool $conditionChecker
	 */
	private function executeHelper(
		Value $target,
		Value $parameter,
		callable $conditionChecker
	): Value {
		if ($target instanceof TupleValue && count($target->values) > 0) {
			if ($parameter instanceof NullValue) {
				$bestV = $target->values[0];
				$best = $bestV->literalValue;
				foreach($target->values as $item) {
					$value = $item->literalValue;
					if ($conditionChecker($value, $best)) {
						$bestV = $item;
						$best = $value;
					}
				}
				return $bestV;
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
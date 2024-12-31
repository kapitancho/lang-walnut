<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class SliceRange implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$pInt = $this->context->typeRegistry->integer(0);
			$pType = $this->context->typeRegistry->record([
				"start" => $pInt,
				"end" => $pInt
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				$endType = $parameterType->types['end'];
				return $this->context->typeRegistry->array(
					$targetType->itemType,
					0,
					$endType->range->maxValue === PlusInfinity::value ? PlusInfinity::value :
					min($targetType->range->maxLength,
					$endType->range->maxValue - $parameterType->types['start']->range->minValue
				));
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
						$start->literalValue,
						$length
					);
					return TypedValue::forValue($this->context->valueRegistry->tuple($values));
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
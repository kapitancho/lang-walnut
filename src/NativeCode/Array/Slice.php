<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnknownProperty;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Slice implements NativeMethod {
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
				"length" => $this->context->typeRegistry->optionalKey($pInt)
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				return $this->context->typeRegistry->array(
					$targetType->itemType,
					0,
					min(
						$targetType->range->maxLength,
						($l = $parameterType->types['length'] ?? null) ? $l->range->maxValue :
							$targetType->range->maxLength
					)
				);
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
				$values = $targetValue->values;
				$start = $parameterValue->valueOf('start');
				try {
					$length = $parameterValue->valueOf('length');
				} catch (UnknownProperty) {
					$length = $this->context->valueRegistry->integer(count($values));
				}
				if (
					$start instanceof IntegerValue &&
					$length instanceof IntegerValue
				) {
					$values = array_slice(
						$values,
						$start->literalValue,
						$length->literalValue
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
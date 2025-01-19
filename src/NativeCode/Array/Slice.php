<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
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

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$pInt = $programRegistry->typeRegistry->integer(0);
			$pType = $programRegistry->typeRegistry->record([
				"start" => $pInt,
				"length" => $programRegistry->typeRegistry->optionalKey($pInt)
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				$parameterType = $this->toBaseType($parameterType);
				return $programRegistry->typeRegistry->array(
					$targetType->itemType,
					0,
					min(
						$targetType->range->maxLength,
						($l = $parameterType->types['length'] ?? null) ? $l->range->maxValue :
							$targetType->range->maxLength
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
					$length = $programRegistry->valueRegistry->integer(count($values));
				}
				if (
					$start instanceof IntegerValue &&
					$length instanceof IntegerValue
				) {
					$values = array_slice(
						$values,
						(string)$start->literalValue,
						(string)$length->literalValue
					);
					return TypedValue::forValue($programRegistry->valueRegistry->tuple($values));
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
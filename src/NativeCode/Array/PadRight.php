<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class PadRight implements NativeMethod {
	use BaseType;	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
        $targetType = $this->toBaseType($targetType);
        $type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof RecordType) {
				$types = $parameterType->types;
				$lengthType = $types['length'] ?? null;
				$valueType = $types['value'] ?? null;
				if ($lengthType instanceof IntegerType || $lengthType instanceof IntegerSubsetType) {
					return $programRegistry->typeRegistry->array(
						$programRegistry->typeRegistry->union([
							$type->itemType,
							$valueType
						]),
						max($type->range->minLength, $lengthType->range->minValue),
						$type->range->maxLength === PlusInfinity::value ||
							$lengthType->range->maxValue === PlusInfinity::value ? PlusInfinity::value :
							max($type->range->maxLength, $lengthType->range->maxValue),
					);
				}
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
		
		if ($targetValue instanceof TupleValue) {
			if ($parameterValue instanceof RecordValue) {
				$values = $targetValue->values;

				$paramValues = $parameterValue->values;
				$length = $paramValues['length'] ?? null;
				$padValue = $paramValues['value'] ?? null;
				if ($length instanceof IntegerValue && $padValue !== null) {
					$result = array_pad(
						$values,
						(string)$length->literalValue,
						$padValue
					);
					return TypedValue::forValue($programRegistry->valueRegistry->tuple($result));
				}
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
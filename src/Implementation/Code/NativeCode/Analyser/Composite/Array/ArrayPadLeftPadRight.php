<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ArrayPadLeftPadRight {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
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
				if ($lengthType) {
					$lengthType = $this->toBaseType($lengthType);
				}
				$valueType = $types['value'] ?? null;
				if ($lengthType instanceof IntegerType) {
					return $typeRegistry->array(
						$typeRegistry->union([
							$type->itemType,
							$valueType
						]),
						max(
							(int)(string)$type->range->minLength,
							$lengthType->numberRange->min === MinusInfinity::value ?
								0 : $lengthType->numberRange->min->value
						),
						$type->range->maxLength === PlusInfinity::value ||
							$lengthType->numberRange->max === PlusInfinity::value ? PlusInfinity::value :
							max(
								(int)(string)$type->range->maxLength,
								$lengthType->numberRange->max->value -
								($lengthType->numberRange->max->inclusive ? 0 : 1)
							),
					);
				}
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter,
		int $padMultiplier
	): Value {
		if ($target instanceof TupleValue) {
			if ($parameter instanceof RecordValue) {
				$values = $target->values;

				$paramValues = $parameter->values;
				$length = $paramValues['length'] ?? null;
				$padValue = $paramValues['value'] ?? null;
				if ($length instanceof IntegerValue && $padValue !== null) {
					$result = array_pad(
						$values,
						$padMultiplier * (int)(string)$length->literalValue,
						$padValue
					);
					return $programRegistry->valueRegistry->tuple($result);
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
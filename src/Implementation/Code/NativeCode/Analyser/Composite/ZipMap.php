<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ZipMap {
	use BaseType;

	private function analyseHelper(
		TypeRegistry $typeRegistry,
		ArrayType|SetType $targetType,
		Type $parameterType,
		bool $unique
	): MapType {
		$itemType = $targetType->itemType;
		if ($itemType->isSubtypeOf($typeRegistry->string())) {
            $parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof TupleType) {
				$parameterType = $parameterType->asArrayType();
			}
            if ($parameterType instanceof ArrayType) {
				return $typeRegistry->map(
					$parameterType->itemType,
					$unique ? min( $targetType->range->minLength, $parameterType->range->minLength) :
						min(1, $targetType->range->minLength, $parameterType->range->minLength),
					match(true) {
						$targetType->range->maxLength === PlusInfinity::value => $parameterType->range->maxLength,
						$parameterType->range->maxLength === PlusInfinity::value => $targetType->range->maxLength,
						default => min($targetType->range->maxLength, $parameterType->range->maxLength)
					},
					$itemType
				);
            }
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
	}

	private function executeHelper(
		ProgramRegistry $programRegistry,
		TupleValue|SetValue $target,
		Value $parameter
	): RecordValue {
		if ($parameter instanceof TupleValue) {
			$values = $target->values;
			$pValues = $parameter->values;
			$result = [];
			foreach($values as $value) {
				if (!($value instanceof StringValue)) {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
				if (count($pValues) === 0) {
					break;
				}
				$pValue = array_shift($pValues);
				$result[$value->literalValue] = $pValue;
			}
			return $programRegistry->valueRegistry->record($result);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter type");
		// @codeCoverageIgnoreEnd
	}

}
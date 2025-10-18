<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Flip implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($programRegistry->typeRegistry->string())) {
				return $programRegistry->typeRegistry->map(
					$programRegistry->typeRegistry->string(),
					min(1, $targetType->range->minLength),
					$targetType->range->maxLength,
				);
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
		$targetValue = $target;

		if ($targetValue instanceof RecordValue) {
			$values = $targetValue->values;

			$rawValues = [];
			foreach($values as $key => $value) {
				if ($value instanceof StringValue) {
					$rawValues[$key] = $value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
			}
			$rawValues = array_flip($rawValues);
			return ($programRegistry->valueRegistry->record(array_map(
				fn($value) => $programRegistry->valueRegistry->string($value),
				$rawValues
			)));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
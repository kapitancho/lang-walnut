<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FilterKeyValue implements NativeMethod {
	use BaseType;

	private function getExpectedType(TypeRegistry $typeRegistry, Type $targetType): Type {
		return $typeRegistry->function(
			$typeRegistry->record([
				'key' => $typeRegistry->string(),
				'value' => $targetType
			]),
			$typeRegistry->boolean
		);
	}

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
			$expectedType = $this->getExpectedType($programRegistry->typeRegistry, $targetType->itemType);
			if ($parameterType->isSubtypeOf($expectedType)) {
				//if ($targetType->itemType()->isSubtypeOf($parameterType->parameterType())) {
					return $programRegistry->typeRegistry->map(
						$targetType->itemType,
						0,
						$targetType->range->maxLength
					);
				//}
				/*throw new AnalyserException(
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$targetType->itemType(),
						$parameterType->parameterType()
					)
				);*/
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

		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof FunctionValue) {
				$values = $targetValue->values;
				$result = [];
				$true = $programRegistry->valueRegistry->true;
				foreach($values as $key => $value) {
					$filterResult = $parameterValue->execute(
						$programRegistry->executionContext,
						$programRegistry->valueRegistry->record([
							'key' => $programRegistry->valueRegistry->string($key),
							'value' => $value
						])
					);
					if ($filterResult->equals($true)) {
						$result[$key] = $value;
					}
				}
				return ($programRegistry->valueRegistry->record($result));
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
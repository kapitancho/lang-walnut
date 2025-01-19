<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FindFirstKeyValue implements NativeMethod {
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
				return $programRegistry->typeRegistry->result(
					$programRegistry->typeRegistry->record([
						'key' => $programRegistry->typeRegistry->string(),
						'value' => $targetType->itemType
					]),
					$programRegistry->typeRegistry->atom(
						new TypeNameIdentifier('ItemNotFound')
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
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof FunctionValue) {
				$values = $targetValue->values;
				$true = $programRegistry->valueRegistry->true;
				foreach($values as $key => $value) {
					$filterResult = $parameterValue->execute(
						$programRegistry->executionContext,
						$val = $programRegistry->valueRegistry->record([
							'key' => $programRegistry->valueRegistry->string($key),
							'value' => $value
						])
					);
					if ($filterResult->equals($true)) {
						return TypedValue::forValue($val);
					}
				}
				return TypedValue::forValue($programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->atom(
						new TypeNameIdentifier('ItemNotFound'),
					)
				));
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
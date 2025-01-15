<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class FindFirst implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		if ($type instanceof MapType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($programRegistry->typeRegistry->boolean)) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					return $programRegistry->typeRegistry->result(
						$type->itemType,
						$programRegistry->typeRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
					);
				}
				throw new AnalyserException(
					sprintf(
						"The parameter type %s of the callback function is not a subtype of %s",
						$type->itemType,
						$parameterType->parameterType
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
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue && $parameterValue instanceof FunctionValue) {
			$values = $targetValue->values;
			$true = $programRegistry->valueRegistry->true;
			foreach($values as $value) {
				$r = $parameterValue->execute($programRegistry->executionContext, $value);
				if ($true->equals($r)) {
					return TypedValue::forValue($value);
				}
			}
			return TypedValue::forValue(
				$programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier('ItemNotFound'))
				)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
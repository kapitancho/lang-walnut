<?php

namespace Walnut\Lang\NativeCode\Record;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class With implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$originalTargetType = $targetType;
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType && $parameterType instanceof RecordType) {
			$recTypes = [...$targetType->types, ...$parameterType->types];
			$result = $programRegistry->typeRegistry->record($recTypes);
			if ($originalTargetType instanceof SubtypeType) {
				$constructorType = $programRegistry->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));
				$validatorMethod = $programRegistry->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier('as' . $originalTargetType->name->identifier)
				);
				$b = $originalTargetType->baseType;
				$errorType = null;
				if ($validatorMethod instanceof Method) {
					$validatorResult = $validatorMethod->analyse($programRegistry, $constructorType, $b);
					if ($validatorResult instanceof ResultType) {
						$errorType = $validatorResult->errorType instanceof NothingType ? null : $validatorResult->errorType;
					}
				}
				if ($result->isSubtypeOf($b)) {
					return $errorType ? $programRegistry->typeRegistry->result(
						$originalTargetType, $errorType
					) : $originalTargetType;
				}
			}
			return $result;
		}
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof MapType) {
				return $programRegistry->typeRegistry->map(
					$programRegistry->typeRegistry->union([
						$targetType->itemType,
						$parameterType->itemType
					]),
					max($targetType->range->minLength, $parameterType->range->minLength),
					$targetType->range->maxLength === PlusInfinity::value ||
						$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						((int)(string)$targetType->range->maxLength) + ((int)(string)$parameterType->range->maxLength)
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
		
		$originalValue = $targetValue;
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof RecordValue) {
				$result = $programRegistry->valueRegistry->record([
					... $targetValue->values, ... $parameterValue->values
				]);
				$r = TypedValue::forValue($result);
				if ($originalValue instanceof SubtypeValue) {
					$constructorType = $programRegistry->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));
					$validatorMethod = $programRegistry->methodRegistry->method(
						$constructorType,
						new MethodNameIdentifier('as' . $originalValue->type->name->identifier)
					);
					if ($validatorMethod instanceof Method) {
						$validatorResult = $validatorMethod->execute(
							$programRegistry,
							TypedValue::forValue($constructorType->value),
							$r,
						);
						$resultValue = $validatorResult->value;
						if ($resultValue instanceof ErrorValue) {
							return $validatorResult;
						}
						$r = TypedValue::forValue($programRegistry->valueRegistry->subtypeValue(
							$originalValue->type->name,
							$result
						));
					}
				}
				return $r;
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
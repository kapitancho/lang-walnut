<?php

namespace Walnut\Lang\NativeCode\Record;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class With implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$originalTargetType = $targetType;
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType && $parameterType instanceof RecordType) {
			$recTypes = [...$targetType->types, ...$parameterType->types];
			$result = $this->context->typeRegistry->record($recTypes);
			if ($originalTargetType instanceof SubtypeType) {
				$constructorType = $this->context->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));
				$validatorMethod = $this->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier('as' . $originalTargetType->name->identifier)
				);
				$b = $originalTargetType->baseType;
				$errorType = null;
				if ($validatorMethod instanceof Method) {
					$validatorResult = $validatorMethod->analyse($constructorType, $b);
					if ($validatorResult instanceof ResultType) {
						$errorType = $validatorResult->errorType instanceof NothingType ? null : $validatorResult->errorType;
					}
				}
				if ($result->isSubtypeOf($b)) {
					return $errorType ? $this->context->typeRegistry->result(
						$originalTargetType, $errorType
					) : $originalTargetType;
				}
			}
			return $result;
		}
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($parameterType instanceof RecordType) {
			$parameterType = $parameterType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof MapType) {
				return $this->context->typeRegistry->map(
					$this->context->typeRegistry->union([
						$targetType->itemType,
						$parameterType->itemType
					]),
					max($targetType->range->minLength, $parameterType->range->minLength),
					$targetType->range->maxLength + $parameterType->range->maxLength
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
		
		$originalValue = $targetValue;
		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof RecordValue) {
				$result = $this->context->valueRegistry->record([
					... $targetValue->values, ... $parameterValue->values
				]);
				$r = TypedValue::forValue($result);
				if ($originalValue instanceof SubtypeValue) {
					$constructorType = $this->context->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));
					$validatorMethod = $this->methodRegistry->method(
						$constructorType,
						new MethodNameIdentifier('as' . $originalValue->type->name->identifier)
					);
					if ($validatorMethod instanceof Method) {
						$validatorResult = $validatorMethod->execute(
							TypedValue::forValue($constructorType->value),
							$r,
						);
						$resultValue = $validatorResult->value;
						if ($resultValue instanceof ErrorValue) {
							return $validatorResult;
						}
						$r = TypedValue::forValue($this->context->valueRegistry->subtypeValue(
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
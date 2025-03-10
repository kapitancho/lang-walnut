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
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
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
		$parameterType = $this->toBaseType($parameterType);
		if ($targetType instanceof RecordType && $parameterType instanceof RecordType) {
			$recTypes = [];
			foreach($targetType->types as $tKey => $tType) {
				$pType = $parameterType->types[$tKey] ?? null;
				if ($tType instanceof OptionalKeyType) {
					$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
						$programRegistry->typeRegistry->optionalKey(
							$programRegistry->typeRegistry->union([
								$tType->valueType,
								$pType->valueType
							])
						) : $pType ?? $programRegistry->typeRegistry->optionalKey(
							$programRegistry->typeRegistry->union([
								$tType->valueType,
								$parameterType->restType
							])
						);
				} else {
					$recTypes[$tKey] = $pType instanceof OptionalKeyType ?
						$programRegistry->typeRegistry->union([
							$tType,
							$pType->valueType
						]): $pType ?? $programRegistry->typeRegistry->union([
						$tType,
						$parameterType->restType
					]);
				}
			}
			foreach ($parameterType->types as $pKey => $pType) {
				$recTypes[$pKey] ??= $pType;
			}
			$result = $programRegistry->typeRegistry->record($recTypes,
				$programRegistry->typeRegistry->union([
					$targetType->restType,
					$parameterType->restType
				])
			);
			if ($originalTargetType instanceof SubsetType) {
				$constructorType = $programRegistry->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));
				$validatorMethod = $programRegistry->methodFinder->methodForType(
					$constructorType,
					new MethodNameIdentifier('as' . $originalTargetType->name->identifier)
				);
				$b = $originalTargetType->valueType;
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
		$targetType = $target->type;
		$targetValue = $target->value;
		$parameterValue = $parameter->value;
		
		if ($targetValue instanceof RecordValue) {
			if ($parameterValue instanceof RecordValue) {
				$result = $programRegistry->valueRegistry->record([
					... $targetValue->values, ... $parameterValue->values
				]);
				$r = TypedValue::forValue($result);
				if ($targetType instanceof SubsetType) {
					$constructorType = $programRegistry->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));
					$validatorMethod = $programRegistry->methodFinder->methodForType(
						$constructorType,
						new MethodNameIdentifier('as' . $targetType->name->identifier)
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
					}
					$r = TypedValue::forValue($result)->withType($targetType);
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
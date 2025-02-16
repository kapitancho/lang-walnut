<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubsetType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Type\UserType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Construct implements NativeMethod {
	use TupleAsRecord;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): Type {
		if ($parameterType instanceof TypeType) {
			$t = $parameterType->refType;
			if ($t instanceof UserType || $t instanceof SubtypeType) {
				$b = $t instanceof UserType ? $t->valueType : $t->baseType;

				$constructorType = $programRegistry->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));

				$constructorMethod = $programRegistry->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier($t->name->identifier)
				);
				$errorTypes = [];
				if ($constructorMethod instanceof Method) {
					$constructorResult = $constructorMethod->analyse(
						$programRegistry,
						$constructorType,
						$targetType
					);
					$constructorMethodReturnType = $constructorResult instanceof ResultType ? $constructorResult->returnType : $constructorResult;
					if ($constructorResult instanceof ResultType) {
						$errorTypes[] = $constructorResult->errorType;
					}
					if (!$constructorMethodReturnType->isSubtypeOf($b)) {
						throw new AnalyserException(
							sprintf(
								"The constructor of %s cannot return a value of type %s , a value of type %s is required",
								$t->name,
								$constructorMethodReturnType,
								$b,
							)
						);
					}
				} else {
					if (!($targetType->isSubtypeOf($b) || (
						$b instanceof RecordType &&
						$targetType instanceof TupleType &&
						$this->isTupleCompatibleToRecord(
							$programRegistry->typeRegistry,
							$targetType,
							$b
						)
					))) {
						throw new AnalyserException(
							sprintf(
								"Invalid constructor value: %s is expected but %s is passed when initializing %s",
								$b,
								$targetType,
								$t->name->identifier
							)
						);
					}
				}
				$validatorMethod = $programRegistry->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier('as' . $t->name->identifier)
				);
				if ($validatorMethod instanceof Method) {
					$validatorResult = $validatorMethod->analyse(
						$programRegistry,
						$constructorType,
						$b
					);
					if ($validatorResult instanceof ResultType) {
						$errorTypes[] = $validatorResult->errorType;
					}
				}
				return count($errorTypes) > 0 ? $programRegistry->typeRegistry->result(
					$t,
					$programRegistry->typeRegistry->union($errorTypes)
				): $t;
			}
			if ($t instanceof ResultType && $t->returnType instanceof NothingType) {
				return $programRegistry->typeRegistry->result(
					$programRegistry->typeRegistry->nothing,
					$targetType
				);
			}
			// @codeCoverageIgnoreStart
			if ($targetType->isSubtypeOf($parameterType->refType)) {
				throw new AnalyserException(
					sprintf("The type %s has no constructor. The constructor parameter can be used directly.", $parameterType->refType)
				);
			}
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(
			sprintf("Invalid parameter type: %s", $parameterType)
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValueType = $target->type;
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		if ($parameterValue instanceof TypeValue) {
			$t = $parameterValue->typeValue;

			if ($t instanceof UserType || $t instanceof SubtypeType) {
				$constructorType = $programRegistry->typeRegistry->atom(new TypeNameIdentifier('Constructor'));
				$constructorMethod = $programRegistry->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier($t->name->identifier)
				);
				if ($constructorMethod instanceof Method) {
					$target = $constructorMethod->execute(
						$programRegistry,
						TypedValue::forValue($constructorType->value),
						$target,
					);
					$resultValue = $target->value;
					if ($resultValue instanceof ErrorValue) {
						return $target;
					}
					$targetValue = $resultValue;
				}
				$validatorMethod = $programRegistry->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier('as' . $t->name->identifier)
				);
				if ($validatorMethod instanceof Method) {
					$result = $validatorMethod->execute(
						$programRegistry,
						TypedValue::forValue($constructorType->value),
						$target,
					);
					$resultValue = $result->value;
					if ($resultValue instanceof ErrorValue) {
						return $result;
					}
				}
			}
			if ($t instanceof SealedType) {
				if ($targetValue instanceof TupleValue) {
					$targetValue = $this->getTupleAsRecord(
						$programRegistry->valueRegistry,
						$targetValue,
						$t->valueType,
					);
					$targetValueType = $targetValue->type;
				}
				if ($targetValueType->isSubtypeOf($t->valueType) ||
					$targetValue->type->isSubtypeOf($t->valueType)
				) {
					return new TypedValue(
						$t,
						$programRegistry->valueRegistry->sealedValue(
							$t->name, $targetValue
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException(sprintf("Invalid target value: %s, %s expected",
					$targetValueType,
					$t->valueType
				));
				// @codeCoverageIgnoreEnd
			}
			if ($t instanceof OpenType) {
				if ($targetValue instanceof TupleValue && $t->valueType instanceof RecordType) {
					$targetValue = $this->getTupleAsRecord(
						$programRegistry->valueRegistry,
						$targetValue,
						$t->valueType,
					);
					$targetValueType = $targetValue->type;
				}
				if ($targetValueType->isSubtypeOf($t->valueType)) {
					return new TypedValue(
						$t,
						$programRegistry->valueRegistry->openValue(
							$t->name, $targetValue
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException(sprintf("Invalid target value: %s, %s expected",
					$targetValueType,
					$t->valueType
				));
				// @codeCoverageIgnoreEnd
			}
			if ($t instanceof SubsetType) {
				if ($targetValue instanceof TupleValue && $t->valueType instanceof RecordType) {
					$targetValue = $this->getTupleAsRecord(
						$programRegistry->valueRegistry,
						$targetValue,
						$t->valueType,
					);
				}
				if ($targetValue->type->isSubtypeOf($t->valueType)) {
					return new TypedValue(
						$t,
						 $targetValue
					);
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException(sprintf("Invalid target value: %s, %s expected",
					$targetValue->type,
					$t->valueType
				));
				// @codeCoverageIgnoreEnd
			}
			if ($t instanceof SubtypeType) {
				if ($targetValue instanceof TupleValue && $t->baseType instanceof RecordType) {
					$targetValue = $this->getTupleAsRecord(
						$programRegistry->valueRegistry,
						$targetValue,
						$t->baseType,
					);
				}
				if ($targetValue->type->isSubtypeOf($t->baseType)) {
					return new TypedValue(
						$t,
						$programRegistry->valueRegistry->subtypeValue(
							$t->name, $targetValue
						)
					);
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException(sprintf("Invalid target value: %s, %s expected",
					$targetValue->type,
					$t->baseType
				));
				// @codeCoverageIgnoreEnd
			}
			if ($t instanceof ResultType && $t->returnType instanceof NothingType) {
				return new TypedValue(
					$t,
					$programRegistry->valueRegistry->error($targetValue)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target/parameter value");
		// @codeCoverageIgnoreEnd
	}
}
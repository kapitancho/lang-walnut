<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\Method;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\SubtypeType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\ErrorValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\TupleAsRecord;

final readonly class Construct implements NativeMethod {
	use TupleAsRecord;

	public function __construct(
		private MethodExecutionContext $context,
		private MethodRegistry $methodRegistry,
	) {}

	public function analyse(Type $targetType, Type $parameterType): Type {
		if ($parameterType instanceof TypeType) {
			$t = $parameterType->refType;
			if ($t instanceof SealedType || $t instanceof SubtypeType) {
				$b = $t instanceof SealedType ? $t->valueType : $t->baseType;

				$constructorType = $this->context->typeRegistry->typeByName(new TypeNameIdentifier('Constructor'));

				$constructorMethod = $this->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier($t->name->identifier)
				);
				$errorTypes = [];
				if ($constructorMethod instanceof Method) {
					$constructorResult = $constructorMethod->analyse($constructorType, $targetType);
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
							$this->context->typeRegistry,
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
				$validatorMethod = $this->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier('as' . $t->name->identifier)
				);
				if ($validatorMethod instanceof Method) {
					$validatorResult = $validatorMethod->analyse($constructorType, $b);
					if ($validatorResult instanceof ResultType) {
						$errorTypes[] = $validatorResult->errorType;
					}
				}
				return count($errorTypes) > 0 ? $this->context->typeRegistry->result(
					$t,
					$this->context->typeRegistry->union($errorTypes)
				): $t;
			}
			if ($t instanceof ResultType && $t->returnType instanceof NothingType) {
				return $this->context->typeRegistry->result(
					$this->context->typeRegistry->nothing,
					$targetType
				);
			}
			if ($targetType->isSubtypeOf($parameterType->refType)) {
				// @codeCoverageIgnoreStart
				throw new AnalyserException(
					sprintf("The type %s has no constructor. The constructor parameter can be used directly.", $parameterType->refType)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(
			sprintf("Invalid parameter type: %s", $parameterType)
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;
		$parameterValue = $parameter->value;

		if ($parameterValue instanceof TypeValue) {
			$t = $parameterValue->typeValue;

			if ($t instanceof SealedType || $t instanceof SubtypeType) {
				$constructorType = $this->context->typeRegistry->atom(new TypeNameIdentifier('Constructor'));
				$constructorMethod = $this->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier($t->name->identifier)
				);
				if ($constructorMethod instanceof Method) {
					$target = $constructorMethod->execute(
						TypedValue::forValue($constructorType->value),
						$target,
					);
					$resultValue = $target->value;
					if ($resultValue instanceof ErrorValue) {
						return $target;
					}
					$targetValue = $resultValue;
				}
				$validatorMethod = $this->methodRegistry->method(
					$constructorType,
					new MethodNameIdentifier('as' . $t->name->identifier)
				);
				if ($validatorMethod instanceof Method) {
					$result = $validatorMethod->execute(
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
						$this->context->valueRegistry,
						$targetValue,
						$t->valueType,
					);
				}
				if ($targetValue instanceof RecordValue && $targetValue->type->isSubtypeOf($t->valueType)) {
					return new TypedValue(
						$t,
						$this->context->valueRegistry->sealedValue(
							$t->name, $targetValue
						)
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
						$this->context->valueRegistry,
						$targetValue,
						$t->baseType,
					);
				}
				if ($targetValue->type->isSubtypeOf($t->baseType)) {
					return new TypedValue(
						$t,
						$this->context->valueRegistry->subtypeValue(
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
					$this->context->valueRegistry->error($targetValue)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target/parameter value");
		// @codeCoverageIgnoreEnd
	}
}
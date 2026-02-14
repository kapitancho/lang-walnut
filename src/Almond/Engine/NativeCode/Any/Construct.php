<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\TupleAsRecord;

/** @extends NativeMethod<Type, TypeType, Value, TypeValue> */
final readonly class Construct extends NativeMethod {
	use TupleAsRecord;

	protected function getValidator(): callable {
		return function(Type $targetType, TypeType $parameterType, mixed $origin): Type|ValidationFailure {
			$cType = $this->valueRegistry->core->constructor->type;
			$refType = $parameterType->refType;
			if ($refType instanceof ResultType && $refType->returnType instanceof NothingType) {
				return $this->typeRegistry->result(
					$this->typeRegistry->nothing,
					$targetType
				);
			}
			if ($refType instanceof OpenType || $refType instanceof SealedType || $refType instanceof EnumerationType) {
				$constructorMethod = $this->methodContext->methodForType(
					$cType,
					$refType->name->asMethodName(),
				);

				$expectedType = $refType instanceof EnumerationType ?
					$this->typeRegistry->string(): $refType->valueType;
				$safeType = $refType instanceof EnumerationType ?
					$this->typeRegistry->stringSubset(
						array_map(
							fn(EnumerationValue $ev): string => $ev->name->identifier,
							$refType->subsetValues
						)
					) : null;

				$cError = null;
				if ($constructorMethod !== UnknownMethod::value) {
					$tType = $constructorMethod instanceof UserlandMethod ?
						$this->adjustParameterType(
							$this->typeRegistry,
							$constructorMethod->parameterType,
							$targetType
						) : $targetType;

					$constructorResult = $constructorMethod->validate(
						$cType,
						$tType,
						$origin
					);
					if ($constructorResult instanceof ValidationFailure) {
						return $constructorResult;
					}
					$cResult = $constructorResult->type;
					if ($cResult instanceof ResultType) {
						$cError = $cResult->errorType;
					}
				} else {
					$tType = $this->adjustParameterType(
						$this->typeRegistry,
						$expectedType,
						$targetType
					);
					if (!$tType->isSubtypeOf($expectedType)) {
						return $this->validationFactory->error(
							ValidationErrorType::invalidParameterType,
							sprintf(
								"The constructor for type '%s' expects a parameter of type '%s', but type '%s' was provided.",
								$refType->name,
								$expectedType,
								$targetType,
							),
							$origin
						);
					}
					if ($safeType && !$tType->isSubtypeOf($safeType)) {
						$cError = $this->typeRegistry->core->unknownEnumerationValue;
					}
				}

				if ($refType instanceof OpenType || $refType instanceof SealedType) {
					$validationMethod = $refType->validator;
					$vError = null;
					if ($validationMethod !== null) {
						$validationResult = $validationMethod->validate(
							$this->typeRegistry->nothing,
							$refType->valueType,
							$origin
						);
						if ($validationResult instanceof ValidationFailure) {
							return $validationResult;
						}
						if ($validationResult->type instanceof ResultType) {
							$vError = $validationResult->type->errorType;
						}
					}
				} else {
					$vError = null;
				}
				$errorType = $cError && $vError ?
					$this->typeRegistry->union([$cError, $vError]) :
					$cError ?? $vError;

				return $errorType ? $this->typeRegistry->result(
					$refType, $errorType
				) : $refType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Construct expects a sealed, an open, or an enumeration type, %s given",
					__CLASS__, $parameterType->refType
				),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(Value $target, TypeValue $parameter): Value {
			$cValue = $this->valueRegistry->core->constructor;

			$parameterType = $parameter->typeValue;
			if ($parameterType instanceof ResultType && $parameterType->returnType instanceof NothingType) {
				return $this->valueRegistry->error($target);
			}
			if ($parameterType instanceof OpenType || $parameterType instanceof SealedType || $parameterType instanceof EnumerationType) {
				$constructorMethod = $this->methodContext->methodForValue(
					$cValue,
					$parameterType->name->asMethodName(),
				);
				$t = $target;
				if ($constructorMethod !== UnknownMethod::value) {
					$tParam = $constructorMethod instanceof UserlandMethod ?
						$this->adjustParameterValue(
							$this->valueRegistry,
							$constructorMethod->parameterType,
							$target
						) : $target;

					$t = $constructorMethod->execute($cValue, $tParam);
					if ($t instanceof ErrorValue) {
						return $t;
					}
				}
				if ($parameterType instanceof OpenType || $parameterType instanceof SealedType) {
					$validationMethod = $parameterType->validator;
					$t = $this->adjustParameterValue(
						$this->valueRegistry,
						$parameterType->valueType,
						$t
					);
					if ($validationMethod !== null) {
						$t = $validationMethod->execute(
							$this->variableScopeFactory->emptyVariableValueScope,
							null,
							$t,
						);
						if ($t instanceof ErrorValue) {
							return $t;
						}
					}
					if ($parameterType instanceof OpenType) {
						return $this->valueRegistry->open($parameterType->name, $t);
					} else {
						return $this->valueRegistry->sealed($parameterType->name, $t);
					}
				}
				if ($t instanceof EnumerationValue) {
					return $t;
				}
				if ($t instanceof StringValue) {
					try {
						return $parameterType->value(new EnumerationValueName($t->literalValue));
					} catch (UnknownEnumerationValue) {
						return $this->valueRegistry->error(
							$this->valueRegistry->core->unknownEnumerationValue(
								$this->valueRegistry->record([
									'enumeration' => $this->valueRegistry->type($parameterType),
									'value' => $t
								])
							)
						);
					}
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value type");
			// @codeCoverageIgnoreEnd
		};
	}

}

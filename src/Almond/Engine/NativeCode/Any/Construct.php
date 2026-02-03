<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Error\UnknownEnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;

final readonly class Construct implements NativeMethod {

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
		private VariableScopeFactory $variableScopeFactory
	) {}

	public function validate(
		TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin
	): ValidationSuccess|ValidationFailure {
		if ($parameterType instanceof TypeType) {
			$cType = $this->valueRegistry->core->constructor->type;
			$refType = $parameterType->refType;
			if ($refType instanceof ResultType && $refType->returnType instanceof NothingType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$this->typeRegistry->nothing,
						$targetType
					)
				);
			}
			if ($refType instanceof OpenType || $refType instanceof SealedType || $refType instanceof EnumerationType) {
				$constructorMethod = $this->methodContext->methodForType(
					$cType,
					$refType->name->asMethodName(),
				);

				$expectedType = $refType instanceof EnumerationType ?
					$this->typeRegistry->stringSubset(
						array_map(
							fn(EnumerationValue $ev): string => $ev->name->identifier,
							$refType->subsetValues
						)
					):
					$refType->valueType;

				$cError = null;
				if ($constructorMethod !== UnknownMethod::value) {
					$constructorResult = $constructorMethod->validate(
						$cType,
						$targetType,
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
					if (!$targetType->isSubtypeOf($expectedType)) {
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
				}

				if ($refType instanceof OpenType || $refType instanceof SealedType) {
					$validationMethod = $refType->validator;
					$vError = null;
					if ($validationMethod !== null) {
						$validationResult = $validationMethod->validate(
							$this->typeRegistry->nothing,
							$refType->valueType
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

				return $this->validationFactory->validationSuccess(
					$errorType ? $this->typeRegistry->result(
						$refType, $errorType
					) : $refType
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Construct expects a sealed, an open, or an enumeration type, %s given",
					__CLASS__, $parameterType->refType
				),
				$origin
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Construct expects a type, %s given", __CLASS__, $parameterType),
			$origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($parameter instanceof TypeValue) {
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
					$t = $constructorMethod->execute($cValue, $target);
					if ($t instanceof ErrorValue) {
						return $t;
					}
				}
				if ($parameterType instanceof OpenType || $parameterType instanceof SealedType) {
					$validationMethod = $parameterType->validator;
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
					} catch (UnknownEnumerationValue) {}
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value type");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}
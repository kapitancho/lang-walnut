<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UnknownMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Blueprint\Value\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory;

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
			$cType = $this->typeRegistry->typeByName(new TypeName('Constructor'));
			$refType = $parameterType->refType;
			if ($refType instanceof ResultType && $refType->returnType instanceof NothingType) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$this->typeRegistry->nothing,
						$targetType
					)
				);
			}
			if ($refType instanceof OpenType || $refType instanceof SealedType) {
				$constructorMethod = $this->methodContext->methodForType(
					$cType,
					$refType->name->asMethodName(),
				);

				$cResult = $targetType;
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
						$cResult = $cResult->returnType;
					}
				}
				if (!$cResult->isSubtypeOf($refType->valueType)) {
					return $this->validationFactory->error(
						ValidationErrorType::invalidReturnType,
						sprintf(
							"Constructor for type '%s' returns type '%s' which is not a subtype of the expected type '%s'.",
							$refType->name,
							$cResult,
							$refType->valueType
						),
						$this
					);
				}

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
				sprintf("[%s] Construct expects a sealed or an open type, %s given",
					__CLASS__, $parameterType
				),
				$this
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidParameterType,
			sprintf("[%s] Construct expects a type, %s given", __CLASS__, $parameterType),
			$this
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($parameter instanceof TypeValue) {
			$cValue = $this->typeRegistry->typeByName(new TypeName('Constructor'))->value;
			$parameterType = $parameter->typeValue;
			if ($parameterType instanceof ResultType && $parameterType->returnType instanceof NothingType) {
				return $this->valueRegistry->error($target);
			}
			if ($parameterType instanceof OpenType || $parameterType instanceof SealedType) {
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
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value type");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}

}
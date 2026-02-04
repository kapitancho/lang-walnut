<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FunctionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithReturnType implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($parameterType->isSubtypeOf(
				$this->typeRegistry->type(
					$this->typeRegistry->any
				)
			)) {
				/** @var TypeType $parameterType */
				if ($refType instanceof ResultType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->result(
								$parameterType->refType,
								$refType->errorType,
							)
						)
					);
				}
				if ($refType instanceof FunctionType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->function(
								$refType->parameterType,
								$parameterType->refType
							)
						)
					);
				}
				if ($refType instanceof MetaType && $refType->value === MetaTypeValue::Function) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type(
							$this->typeRegistry->function(
								$this->typeRegistry->nothing,
								$parameterType->refType
							)
						)
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidTargetType,
					sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof TypeValue) {
			$typeValue = $this->toBaseType($target->typeValue);
			/** @var TypeValue $parameter */
			if ($typeValue instanceof ResultType) {
				$result = $this->typeRegistry->result(
					$parameter->typeValue,
					$typeValue->errorType,
				);
				return $this->valueRegistry->type($result);
			}
			if ($typeValue instanceof FunctionType) {
				$result = $this->typeRegistry->function(
					$typeValue->parameterType,
					$parameter->typeValue,
				);
				return $this->valueRegistry->type($result);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}

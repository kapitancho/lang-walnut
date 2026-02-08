<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Integer;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NumericRangeHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;
	use NumericRangeHelper;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof IntegerType) {
			$parameterType = $this->toBaseType($parameterType);

			if ($parameterType instanceof IntegerType || $parameterType instanceof RealType) {
				$subsetType = $this->getModuloSubsetType(
					$targetType, $parameterType
				);
				if ($subsetType !== null) {
					return $this->validationFactory->validationSuccess($subsetType);
				}

				$includesZero = $parameterType->contains(0);
				$returnType = $parameterType instanceof IntegerType ?
					$this->typeRegistry->integer() : $this->typeRegistry->real();

				return $this->validationFactory->validationSuccess(
					$includesZero ? $this->typeRegistry->result(
						$returnType,
						$this->typeRegistry->core->notANumber
					) : $returnType
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		}
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof IntegerValue) {
			if ($parameter instanceof IntegerValue) {
				if ((int)(string)$parameter->literalValue === 0) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->notANumber
					);
				}
				return $this->valueRegistry->integer(
					$target->literalValue % $parameter->literalValue
				);
			}
			if ($parameter instanceof RealValue) {
				if ((float)(string)$parameter->literalValue === 0.0) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->notANumber
					);
				}
				return $this->valueRegistry->real(
					fmod((float)(string)$target->literalValue, (float)(string)$parameter->literalValue)
				);
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

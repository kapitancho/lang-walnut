<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithRange implements NativeMethod {

	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(TypeInterface $targetType, TypeInterface $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		if ($targetType instanceof TypeType) {
			$refType = $this->toBaseType($targetType->refType);
			if ($refType instanceof IntegerType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->core->integerRange
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->integer())
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
			}
			if ($refType instanceof RealType) {
				if ($parameterType->isSubtypeOf(
					$this->typeRegistry->core->realRange
				)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->real())
					);
				}
				// @codeCoverageIgnoreStart
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					origin: $origin
				);
				// @codeCoverageIgnoreEnd
			}
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
			if ($typeValue instanceof IntegerType) {
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->core->integerRange
				)) {
					$range = $parameter->value->values;
					$minValue = $range['minValue'];
					$maxValue = $range['maxValue'];
					$result = $this->typeRegistry->integer(
						$minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
						$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return $this->valueRegistry->type($result);
				}
			}
			if ($typeValue instanceof RealType) {
				if ($parameter->type->isSubtypeOf(
					$this->typeRegistry->core->realRange
				)) {
					$range = $parameter->value->values;
					$minValue = $range['minValue'];
					$maxValue = $range['maxValue'];
					$result = $this->typeRegistry->real(
						$minValue instanceof RealValue || $minValue instanceof IntegerValue ? $minValue->literalValue : MinusInfinity::value,
						$maxValue instanceof RealValue || $maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
					);
					return $this->valueRegistry->type($result);
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}

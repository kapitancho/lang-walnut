<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type as TypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithLengthRange implements NativeMethod {

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
				$this->typeRegistry->core->lengthRange
			)) {
				if ($refType instanceof StringType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->string())
					);
				}
				if ($refType instanceof ArrayType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->array($refType->itemType))
					);
				}
				if ($refType instanceof MapType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->map($refType->itemType, 0, PlusInfinity::value, $refType->keyType))
					);
				}
				if ($refType instanceof SetType) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->type($this->typeRegistry->set($refType->itemType))
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
			if ($parameter->type->isSubtypeOf(
				$this->typeRegistry->core->lengthRange
			)) {
				if (
					$typeValue instanceof StringType ||
					$typeValue instanceof ArrayType ||
					$typeValue instanceof MapType ||
					$typeValue instanceof SetType
				) {
					$range = $parameter->value->values;
					$minValue = $range['minLength'];
					$maxValue = $range['maxLength'];

					if ($typeValue instanceof StringType) {
						$result = $this->typeRegistry->string(
							$minValue->literalValue,
							$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
						);
						return $this->valueRegistry->type($result);
					}
					if ($typeValue instanceof ArrayType) {
						$result = $this->typeRegistry->array(
							$typeValue->itemType,
							$minValue->literalValue,
							$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
						);
						return $this->valueRegistry->type($result);
					}
					if ($typeValue instanceof MapType) {
						$result = $this->typeRegistry->map(
							$typeValue->itemType,
							$minValue->literalValue,
							$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
							$typeValue->keyType
						);
						return $this->valueRegistry->type($result);
					}
					if ($typeValue instanceof SetType) {
						$result = $this->typeRegistry->set(
							$typeValue->itemType,
							$minValue->literalValue,
							$maxValue instanceof IntegerValue ? $maxValue->literalValue : PlusInfinity::value,
						);
						return $this->valueRegistry->type($result);
					}
				}
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid parameter value");
		// @codeCoverageIgnoreEnd
	}
}

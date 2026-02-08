<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
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

final readonly class BinaryMultiply implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			if ($targetType->restType instanceof NothingType && $parameterType instanceof IntegerType) {
				$minValue = $parameterType->numberRange->min;
				$maxValue = $parameterType->numberRange->max;
				if (
					$minValue !== MinusInfinity::value && $minValue->value >= 0 &&
					$maxValue !== PlusInfinity::value && (string)$maxValue->value === (string)$minValue->value
				) {
					$result = [];
					for ($i = 0; $i < $minValue->value; $i++) {
						$result = array_merge($result, $targetType->types);
					}
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->tuple($result, null)
					);
				}
			}
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof IntegerType) {
				$minValue = $parameterType->numberRange->min;
				if ($minValue !== MinusInfinity::value && $minValue->value >= 0) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->array(
							$targetType->itemType,
							$targetType->range->minLength * $minValue->value,
							$targetType->range->maxLength === PlusInfinity::value ||
								$parameterType->numberRange->max === PlusInfinity::value ?
									PlusInfinity::value :
									$targetType->range->maxLength * $parameterType->numberRange->max->value
						)
					);
				}
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
		if ($target instanceof TupleValue) {
			if ($parameter instanceof IntegerValue && $parameter->literalValue >= 0) {
				$result = [];
				for ($i = 0; $i < (int)(string)$parameter->literalValue; $i++) {
					$result = array_merge($result, $target->values);
				}
				return $this->valueRegistry->tuple($result);
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

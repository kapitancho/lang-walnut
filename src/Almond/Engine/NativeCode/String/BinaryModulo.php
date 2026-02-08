<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
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

final readonly class BinaryModulo implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			$parameterType = $this->toBaseType($parameterType);
			if (
				$parameterType instanceof IntegerType &&
				$parameterType->numberRange->min !== MinusInfinity::value &&
				$parameterType->numberRange->min->value >= 1
			) {
				if (
					$targetType->range->maxLength !== PlusInfinity::value &&
					$parameterType->numberRange->min->value > $targetType->range->maxLength
				) {
					return $this->validationFactory->validationSuccess($targetType);
				}
				if (
					$targetType->range->maxLength !== PlusInfinity::value &&
					(string)$targetType->range->minLength === (string)$targetType->range->maxLength &&
					$parameterType->numberRange->max !== PlusInfinity::value &&
					(string)$parameterType->numberRange->max->value === (string)$parameterType->numberRange->min->value
				) {
					$size = $targetType->range->maxLength->mod($parameterType->numberRange->min->value);
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->string($size, $size)
					);
				}

				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->string(
						0,
						match(true) {
							$parameterType->numberRange->max === PlusInfinity::value => $targetType->range->maxLength,
							$targetType->range->maxLength === PlusInfinity::value => max(
								0,
								$parameterType->numberRange->max->value->sub(1),
							),
							default => max(
								0,
								min(
									$parameterType->numberRange->max->value->sub(1),
									$targetType->range->maxLength
								)
							)
						}
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof StringValue) {
			if ($parameter instanceof IntegerValue) {
				$splitLength = (int)(string)$parameter->literalValue;
				if ($splitLength > 0) {
					$result = mb_str_split($target->literalValue, $splitLength);
					$last = $result[array_key_last($result)] ?? '';
					return $this->valueRegistry->string(
						mb_strlen($last) < $splitLength ? $last : ''
					);
				}
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

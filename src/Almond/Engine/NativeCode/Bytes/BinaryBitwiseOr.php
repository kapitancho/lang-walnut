<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class BinaryBitwiseOr implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof BytesType) {
				$minLength = max($targetType->range->minLength, $parameterType->range->minLength);
				$maxLength = max($targetType->range->maxLength, $parameterType->range->maxLength);
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->bytes($minLength, $maxLength)
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
		if ($target instanceof BytesValue) {
			if ($parameter instanceof BytesValue) {
				$targetBytes = $target->literalValue;
				$paramBytes = $parameter->literalValue;
				$targetLen = strlen($targetBytes);
				$paramLen = strlen($paramBytes);

				$maxLen = max($targetLen, $paramLen);
				if ($targetLen < $maxLen) {
					$targetBytes = str_pad($targetBytes, $maxLen, "\x00", STR_PAD_LEFT);
				}
				if ($paramLen < $maxLen) {
					$paramBytes = str_pad($paramBytes, $maxLen, "\x00", STR_PAD_LEFT);
				}

				$result = '';
				for ($i = 0; $i < $maxLen; $i++) {
					$result .= chr(ord($targetBytes[$i]) | ord($paramBytes[$i]));
				}

				return $this->valueRegistry->bytes($result);
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

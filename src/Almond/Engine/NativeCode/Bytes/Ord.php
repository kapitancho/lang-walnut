<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, NullType, BytesValue, NullValue> */
final readonly class Ord extends NativeMethod {

	protected function getValidator(): callable {
		return function(BytesType $targetType, NullType $parameterType, Expression|null $origin): IntegerType|ValidationFailure {
			if ((string)$targetType->range->minLength === '1' &&
				(string)$targetType->range->maxLength === '1'
			) {
				return $this->typeRegistry->integer(0, 255);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(BytesValue $target, NullValue $parameter): IntegerValue =>
			$this->valueRegistry->integer(ord((string)$target->literalValue));
	}

}

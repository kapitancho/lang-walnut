<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, ArrayType|TupleType, BytesValue, TupleValue> */
final readonly class ConcatList extends NativeMethod {

	protected function getValidator(): callable {
		return function(BytesType $targetType, ArrayType|TupleType $parameterType, mixed $origin): BytesType|ValidationFailure {
			if ($parameterType instanceof TupleType) {
				$parameterType = $parameterType->asArrayType();
			}
			$itemType = $this->toBaseType($parameterType->itemType);
			if ($itemType instanceof BytesType) {
				return $this->typeRegistry->bytes(
					$targetType->range->minLength + $parameterType->range->minLength * $itemType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->range->maxLength === PlusInfinity::value ||
					$itemType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxLength + $parameterType->range->maxLength * $itemType->range->maxLength,
				);
			}
			if ($itemType->isSubtypeOf($this->typeRegistry->bytes())) {
				return $this->typeRegistry->bytes();
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, TupleValue $parameter): BytesValue {
			$result = $target->literalValue;
			foreach($parameter->values as $value) {
				if ($value instanceof BytesValue) {
					$result .= $value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid parameter value");
					// @codeCoverageIgnoreEnd
				}
			}
			return $this->valueRegistry->bytes($result);
		};
	}
}

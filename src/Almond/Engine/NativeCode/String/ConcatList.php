<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, ArrayType|TupleType, StringValue, TupleValue> */
final readonly class ConcatList extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, ArrayType|TupleType $parameterType, mixed $origin): StringType|ValidationFailure {
			if ($parameterType instanceof TupleType) {
				$parameterType = $parameterType->asArrayType();
			}
			$itemType = $this->toBaseType($parameterType->itemType);
			if ($itemType instanceof StringType) {
				return $this->typeRegistry->string(
					$targetType->range->minLength + $parameterType->range->minLength * $itemType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->range->maxLength === PlusInfinity::value ||
					$itemType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxLength + $parameterType->range->maxLength * $itemType->range->maxLength,
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, TupleValue $parameter): StringValue {
			$result = $target->literalValue;
			foreach($parameter->values as $value) {
				if ($value instanceof StringValue) {
					$result .= $value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid parameter value");
					// @codeCoverageIgnoreEnd
				}
			}
			return $this->valueRegistry->string($result);
		};
	}
}

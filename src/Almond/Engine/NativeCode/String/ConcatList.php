<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, ArrayType, StringValue, TupleValue> */
final readonly class ConcatList extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		/** @var ArrayType $parameterType */
		return $this->toBaseType($parameterType->itemType) instanceof StringType ?
			null : sprintf(
				"Expected an Array<String> as parameter type, got %s",
				$parameterType
			);
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, ArrayType $parameterType): StringType {
			$itemType = $this->toBaseType($parameterType->itemType);
			/** @var StringType $itemType */
			return $this->typeRegistry->string(
				$targetType->range->minLength + $parameterType->range->minLength * $itemType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ||
				$parameterType->range->maxLength === PlusInfinity::value ||
				$itemType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
				$targetType->range->maxLength + $parameterType->range->maxLength * $itemType->range->maxLength,
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

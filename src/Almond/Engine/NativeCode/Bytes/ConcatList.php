<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, ArrayType, BytesValue, TupleValue> */
final readonly class ConcatList extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->array($this->typeRegistry->bytes())
		) ? null : sprintf("Parameter type %s is not a subtype of Array<Bytes>", $parameterType);
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, ArrayType $parameterType): BytesType {
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
			return $this->typeRegistry->bytes();
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

<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, BytesType, BytesValue, BytesValue> */
final readonly class Split extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		return $parameterType->isSubtypeOf($this->typeRegistry->bytes(1));
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, BytesType $parameterType): ArrayType {
			return $this->typeRegistry->array(
				$targetType,
				$targetType->range->minLength > 0 ? 1 : 0,
				$targetType->range->maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, BytesValue $parameter): TupleValue {
			$result = explode($parameter->literalValue, $target->literalValue);
			return $this->valueRegistry->tuple(
				array_map(fn(string $piece): BytesValue =>
					$this->valueRegistry->bytes($piece), $result)
			);
		};
	}
}

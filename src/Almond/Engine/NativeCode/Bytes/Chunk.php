<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, IntegerType, BytesValue, IntegerValue> */
final readonly class Chunk extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, IntegerType $parameterType): ArrayType =>
			$this->typeRegistry->array(
				$this->typeRegistry->bytes(
					min(1, $targetType->range->minLength),
					$parameterType->numberRange->max === PlusInfinity::value ?
						PlusInfinity::value : $parameterType->numberRange->max->value
				),
				$targetType->range->minLength > 0 ? 1 : 0,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, IntegerValue $parameter): TupleValue {
			$splitLength = (int)(string)$parameter->literalValue;
			$result = str_split($target->literalValue, $splitLength);
			return $this->valueRegistry->tuple(
				array_map(fn(string $piece): BytesValue =>
					$this->valueRegistry->bytes($piece), $result)
			);
		};
	}
}

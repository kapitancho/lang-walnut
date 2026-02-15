<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, StringType, StringValue> */
final readonly class CombineAsString extends ArrayNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, StringType $parameterType): StringType =>
			$this->typeRegistry->string(
				$parameterType->range->minLength->mul(
					max(0, $targetType->range->minLength - 1)
				),
			);
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, StringValue $parameter): StringValue {
			$result = [];
			foreach($target->values as $value) {
				/** @var StringValue $value */
				$result[] = $value->literalValue;
			}
			return $this->valueRegistry->string(implode($parameter->literalValue, $result));
		};
	}

}

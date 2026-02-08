<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<AnyType, NullType, NullValue> */
readonly class MapValues extends MapNativeMethod {

	protected function getValidator(): callable {
		return fn(MapType $targetType, NullType $parameterType, Expression|null $origin): ArrayType =>
			$this->typeRegistry->array(
				$targetType->itemType,
				$targetType->range->minLength,
				$targetType->range->maxLength
			);
	}

	protected function getExecutor(): callable {
		return fn(RecordValue $target, NullValue $parameter): TupleValue =>
			$this->valueRegistry->tuple($target->values);
	}

}
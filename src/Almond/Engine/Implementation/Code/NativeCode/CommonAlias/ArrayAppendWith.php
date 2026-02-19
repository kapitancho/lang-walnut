<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\MinusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, ArrayType|TupleType, TupleValue> */
abstract readonly class ArrayAppendWith extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType $targetType, ArrayType|TupleType $parameterType): ArrayType {
			$parameterType = $parameterType instanceof TupleType ? $parameterType->asArrayType() : $parameterType;
			return $this->typeRegistry->array(
				$this->typeRegistry->union([$targetType->itemType, $parameterType->itemType]),
				$targetType->range->minLength + $parameterType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value || $parameterType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value : $targetType->range->maxLength + $parameterType->range->maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return fn(TupleValue $target, TupleValue $parameter): TupleValue =>
		$this->valueRegistry->tuple(array_merge($target->values, $parameter->values));
	}

}
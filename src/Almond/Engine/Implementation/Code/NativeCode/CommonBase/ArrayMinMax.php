<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, NullType, NullValue> */
abstract readonly class ArrayMinMax extends ArrayNativeMethod {

	protected function validateTargetType(Type $targetType, mixed $origin): null|string {
		if ($result = parent::validateTargetType($targetType, $origin)) {
			return $result;
		}
		if (
			($targetType instanceof TupleType && count($targetType->types) === 0) ||
			($targetType instanceof ArrayType && (string)$targetType->range->minLength === '0')
		) {
			return "The array must have at least one item.";
		}
		return null;
	}

	protected function getExpectedArrayItemType(): Type {
		return $this->typeRegistry->real();
	}

}
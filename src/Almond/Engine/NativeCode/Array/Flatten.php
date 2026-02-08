<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

/** @extends ArrayNativeMethod<Type, NullType, NullValue> */
final readonly class Flatten extends ArrayNativeMethod {

	protected function isTargetItemTypeValid(Type $targetItemType, mixed $origin): bool {
		return $targetItemType->isSubtypeOf($this->typeRegistry->array());
	}

	protected function getValidator(): callable {
		return function(ArrayType $targetType, NullType $parameterType): ArrayType {
			$itemType = $targetType->itemType;
			if ($itemType instanceof ArrayType) {
				return $this->typeRegistry->array(
					$itemType->itemType,
					((int)(string)$targetType->range->minLength) * ((int)(string)$itemType->range->minLength),
					$targetType->range->maxLength === PlusInfinity::value ||
						$itemType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value :
						((int)(string)$targetType->range->maxLength) * ((int)(string)$itemType->range->maxLength),
				);
			}
			return $this->typeRegistry->array();
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): TupleValue {
			$result = [];
			foreach ($target->values as $value) {
				if ($value instanceof TupleValue) {
					$result = array_merge($result, $value->values);
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
			}
			return $this->valueRegistry->tuple($result);
		};
	}

}

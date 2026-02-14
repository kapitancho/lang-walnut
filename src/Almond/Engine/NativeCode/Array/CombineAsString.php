<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, StringType, TupleValue, StringValue> */
final readonly class CombineAsString extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		if (!($targetType instanceof ArrayType || $targetType instanceof TupleType)) {
			return false;
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		return $type->itemType->isSubtypeOf($this->typeRegistry->string());
	}

	protected function getValidator(): callable {
		return fn(ArrayType|TupleType $targetType, StringType $parameterType): StringType =>
			$this->typeRegistry->string(
				$parameterType->range->minLength->mul(
					max(0, ($targetType instanceof TupleType ? $targetType->asArrayType() : $targetType)->range->minLength - 1)
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

<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class POP extends MutableNativeMethod {

	protected function getValidator(): callable {
		return function(MutableType $targetType, NullType $parameterType, Expression|null $origin): ResultType {
			$vt = $this->toBaseType($targetType->valueType);
			return $this->typeRegistry->result(
				match(true) {
					$vt instanceof ArrayType => $vt->itemType,
					$vt instanceof TupleType => $vt->restType,
					default => $this->typeRegistry->any
				},
				$this->typeRegistry->core->itemNotFound
			);
		};
	}

	protected function isTargetValueTypeValid(Type $targetValueType, Expression|null $origin): bool {
		return
			($targetValueType instanceof ArrayType && (int)(string)$targetValueType->range->minLength === 0) ||
			($targetValueType instanceof TupleType && count($targetValueType->types) === 0);

	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, NullValue $parameter): Value {
			/** @var TupleValue $targetValue */
			$targetValue = $target->value;
			$values = $targetValue->values;
			if (count($values) > 0) {
				$value = array_pop($values);
				$target->value = $this->valueRegistry->tuple($values);
				return $value;
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->itemNotFound
			);
		};
	}
}

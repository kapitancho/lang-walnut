<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<MapType, Type, Value> */
final readonly class KEYSORT extends MutableNativeMethod {

	protected function isTargetValueTypeValid(Type $targetValueType, mixed $origin): bool {
		return $targetValueType instanceof MapType;
	}

	protected function getValidator(): callable {
		return function(MutableType $targetType, Type $parameterType, mixed $origin): MutableType|ValidationFailure {
			$pType = $this->typeRegistry->union([
				$this->typeRegistry->null,
				$this->typeRegistry->record([
					'reverse' => $this->typeRegistry->boolean
				], null)
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				return $targetType;
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf(
					"The parameter type %s is not a subtype of %s",
					$parameterType,
					$pType
				),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(MutableValue $target, Value $parameter): MutableValue {
			/** @var RecordValue $v */
			$v = $target->value;
			$reverse = false;
			if ($parameter instanceof RecordValue) {
				$rev = $parameter->values['reverse'] ?? null;
				if ($rev instanceof BooleanValue) {
					$reverse = $rev->literalValue;
				}
			}
			$values = $v->values;
			$reverse ? krsort($values, SORT_STRING) : ksort($values, SORT_STRING);
			$target->value = $this->valueRegistry->record($values);
			return $target;
		};
	}

}

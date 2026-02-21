<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MutableNativeMethod;

/** @extends MutableNativeMethod<MapType, Type, Value> */
final readonly class KEYSORT extends MutableNativeMethod {

	protected function validateTargetValueType(Type $valueType): null|string {
		return $valueType->isSubtypeOf($this->typeRegistry->map()) ?
			null :
			sprintf("The value type of the target set must be a subtype of Map, got %s", $valueType);
	}

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$pType = $this->typeRegistry->union([
			$this->typeRegistry->null,
			$this->typeRegistry->record([
				'reverse' => $this->typeRegistry->boolean
			], null)
		]);
		return $parameterType->isSubtypeOf($pType) ?
			null :
			sprintf(
				"The parameter type %s is not a subtype of %s",
				$parameterType,
				$pType
			);
	}

	protected function getValidator(): callable {
		return fn(MutableType $targetType, Type $parameterType, mixed $origin): MutableType => $targetType;
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

<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class KeySort extends MapNativeMethod {

	protected function getValidator(): callable {
		return function(MapType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
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
				sprintf("The parameter type %s is not a subtype of %s", $parameterType, $pType),
				$origin
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, Value $parameter): RecordValue {
			$reverse = false;
			if ($parameter instanceof RecordValue) {
				$rev = $parameter->values['reverse'] ?? null;
				if ($rev instanceof BooleanValue) {
					$reverse = $rev->literalValue;
				}
			}
			$values = $target->values;
			$reverse ? krsort($values, SORT_STRING) : ksort($values, SORT_STRING);
			return $this->valueRegistry->record($values);
		};
	}

}

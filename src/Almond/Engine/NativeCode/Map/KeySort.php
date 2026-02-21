<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\MapNativeMethod;

/** @extends MapNativeMethod<Type, Type, Value> */
final readonly class KeySort extends MapNativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		$pType = $this->typeRegistry->union([
			$this->typeRegistry->null,
			$this->typeRegistry->record([
				'reverse' => $this->typeRegistry->boolean
			], null)
		]);
		return $parameterType->isSubtypeOf($pType) ?
			null :
			sprintf("The parameter type %s is not a subtype of %s", $parameterType, $pType);
	}

	protected function getValidator(): callable {
		return fn(MapType $targetType, Type $parameterType, mixed $origin): MapType => $targetType;
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

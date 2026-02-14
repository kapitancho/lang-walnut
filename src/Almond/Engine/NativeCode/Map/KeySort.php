<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, Type, RecordValue, Value> */
final readonly class KeySort extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type {
		return $targetType instanceof MapType || $targetType instanceof RecordType;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, Type $parameterType, mixed $origin): Type|ValidationFailure {
			$type = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
			$pType = $this->typeRegistry->union([
				$this->typeRegistry->null,
				$this->typeRegistry->record([
					'reverse' => $this->typeRegistry->boolean
				], null)
			]);
			if ($parameterType->isSubtypeOf($pType)) {
				return $type;
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

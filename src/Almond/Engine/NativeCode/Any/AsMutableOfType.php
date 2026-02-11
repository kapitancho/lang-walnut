<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Any;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TypeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, TypeType, Value, TypeValue> */
final readonly class AsMutableOfType extends NativeMethod {

	protected function getValidator(): callable {
		return fn(Type $targetType, TypeType $parameterType, mixed $origin): Type =>
			$this->typeRegistry->result(
				$this->typeRegistry->metaType(MetaTypeValue::MutableValue),
				$this->typeRegistry->core->castNotAvailable
			);
	}

	protected function getExecutor(): callable {
		return function(Value $target, TypeValue $parameter): Value {
			if ($target->type->isSubtypeOf($parameter->typeValue)) {
				return $this->valueRegistry->mutable(
					$parameter->typeValue,
					$target
				);
			}
			return $this->valueRegistry->error(
				$this->valueRegistry->core->castNotAvailable(
					$this->valueRegistry->record([
						'from' => $this->valueRegistry->type($target->type),
						'to' => $this->valueRegistry->type($parameter->typeValue)
					])
				)
			);
		};
	}
}
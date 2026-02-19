<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, StringType, StringValue, StringValue> */
final readonly class Split extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool|Type {
		if (!parent::isParameterTypeValid($parameterType, $validator, $targetType)) {
			return false;
		}
		/** @var StringType $parameterType */
		return $parameterType->range->minLength > 0;
	}

	protected function getValidator(): callable {
		return function(StringType $targetType, StringType $parameterType): ArrayType {
			return $this->typeRegistry->array(
				$targetType,
				$targetType->range->minLength > 0 ? 1 : 0,
				$targetType->range->maxLength
			);
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, StringValue $parameter): TupleValue {
			$result = explode($parameter->literalValue, $target->literalValue);
			return $this->valueRegistry->tuple(
				array_map(fn(string $piece): StringValue =>
					$this->valueRegistry->string($piece), $result)
			);
		};
	}
}

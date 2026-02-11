<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Enumeration;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MetaType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\MetaTypeValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<EnumerationSubsetType|MetaType, NullType, EnumerationValue, NullValue> */
final readonly class TextValue extends NativeMethod {

	protected function getValidator(): callable {
		return function (EnumerationSubsetType|MetaType $targetType, NullType $parameterType): StringType|ValidationFailure {
			if ($targetType instanceof MetaType) {
				return $this->typeRegistry->string(1, 999999);
			}
			$min = 0;
			$max = 999999;
			foreach($targetType->subsetValues as $value) {
				$l = mb_strlen($value->name);
				$min = min($min, $l);
				$max = max($max, $l);
			}
			return $this->typeRegistry->string($min, $max);
		};
	}

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool {
		if (!parent::isTargetTypeValid($targetType, $validator, $origin)) {
			return false;
		}
		if ($targetType instanceof MetaType && $targetType->value !== MetaTypeValue::Enumeration) {
			return false;
		}
		return true;
	}

	protected function getExecutor(): callable {
		return fn(EnumerationValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string($target->name->identifier);
	}
}
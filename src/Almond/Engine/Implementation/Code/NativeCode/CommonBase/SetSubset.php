<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonBase;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\SetNativeMethod;

/** @extends SetNativeMethod<AnyType, SetType, SetValue> */
abstract readonly class SetSubset extends SetNativeMethod {

	protected function getValidator(): callable {
		return fn(SetType $targetType, SetType $parameterType, mixed $origin) =>
			$this->typeRegistry->boolean;
	}

	protected function getExecutor(): callable {
		return fn(SetValue $target, SetValue $parameter) =>
			$this->valueRegistry->boolean($this->checkRelation($target, $parameter));
	}

	abstract protected function checkRelation(SetValue $target, SetValue $parameter): bool;

	// To be used in the subclasses to implement both subset and superset logic.
	protected function isSubset(SetValue $value, SetValue $ofValue, bool $strict = false): bool {
		$isSubset = !array_any(
			array_keys($value->valueSet),
			fn($key) => !array_key_exists($key, $ofValue->valueSet)
		);
		return $isSubset && (!$strict || count($value->values) < count($ofValue->values));
	}

}
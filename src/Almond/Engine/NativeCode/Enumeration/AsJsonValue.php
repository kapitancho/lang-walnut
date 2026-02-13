<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Enumeration;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<EnumerationSubsetType, NullType, EnumerationValue, NullValue> */
final readonly class AsJsonValue extends NativeMethod {

	protected function getValidator(): callable {
		return fn(EnumerationSubsetType $targetType, NullType $parameterType): Type =>
			$this->typeRegistry->core->jsonValue;
	}

	protected function getExecutor(): callable {
		return fn(EnumerationValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string($target->name->identifier);
	}
}

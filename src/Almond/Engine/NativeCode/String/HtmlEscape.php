<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue */
final readonly class HtmlEscape extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, NullType $parameterType, mixed $origin): StringType =>
			$this->typeRegistry->string(
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ?
					PlusInfinity::value :
					$targetType->range->maxLength * 6 // max expansion when escaping HTML entities
			);
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, NullValue $parameter): StringValue =>
			$this->valueRegistry->string(htmlspecialchars($target->literalValue));
	}

}

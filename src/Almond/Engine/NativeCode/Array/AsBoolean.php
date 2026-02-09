<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BooleanType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\FalseType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TrueType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\ArrayNativeMethod;

final readonly class AsBoolean extends ArrayNativeMethod {

	protected function getValidator(): callable {
		return fn(ArrayType $targetType, NullType $parameterType): BooleanType|TrueType|FalseType =>
			match(true) {
				$targetType->range->minLength > 0 => $this->typeRegistry->true,
				$targetType->range->maxLength !== PlusInfinity::value &&
					(string)$targetType->range->maxLength == '0' => $this->typeRegistry->false,
				default => $this->typeRegistry->boolean,
			};
	}

	protected function getExecutor(): callable {
		return fn(TupleValue $target, NullValue $parameter): BooleanValue =>
			$this->valueRegistry->boolean(count($target->values) > 0);
	}

}
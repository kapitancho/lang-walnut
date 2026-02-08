<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\CommonAlias;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
readonly class StringReverse extends NativeMethod {

	protected function getValidator(): callable {
		return function(StringType $targetType, NullType $parameterType, Expression|null $origin): StringType {
			if ($targetType instanceof StringSubsetType) {
				return $this->typeRegistry->stringSubset(
					array_map(
						$this->reverse(...),
						$targetType->subsetValues
					)
				);
			}
			return $targetType;
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, NullValue $parameter): StringValue {
			return $this->valueRegistry->string($this->reverse($target->literalValue));
		};
	}

	private function reverse(string $str): string {
		preg_match_all('/./us', $str, $matches);
		return implode('', array_reverse($matches[0]));
	}

}

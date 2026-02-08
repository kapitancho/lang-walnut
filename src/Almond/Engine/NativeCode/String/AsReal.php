<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
final readonly class AsReal extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, NullType $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->real(),
				$this->typeRegistry->core->notANumber
			);
	}

	protected function getExecutor(): callable {
		return fn(StringValue $target, NullValue $parameter): Value =>
			(string)($result = (float)$target->literalValue) === $target->literalValue ?
				$this->valueRegistry->real($result) :
				$this->valueRegistry->error(
					$this->valueRegistry->core->notANumber
				);
	}

}

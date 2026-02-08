<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, StringType, StringValue, StringValue> */
final readonly class LastPositionOf extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, StringType $parameterType): ResultType =>
		$this->typeRegistry->result(
			$this->typeRegistry->integer(0,
				$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxLength - $parameterType->range->minLength
			),
			$this->typeRegistry->core->substringNotInString
		);
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, StringValue $parameter): IntegerValue|ErrorValue {
			$result = mb_strrpos($target->literalValue, $parameter->literalValue);
			return $result === false ?
				$this->valueRegistry->error(
					$this->valueRegistry->core->substringNotInString
				) : $this->valueRegistry->integer($result);
		};
	}

}


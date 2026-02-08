<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\ErrorValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, BytesType, BytesValue, BytesValue> */
final readonly class LastPositionOf extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, BytesType $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->integer(0,
					$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxLength - $parameterType->range->minLength
				),
				$this->typeRegistry->core->sliceNotInBytes
			);
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, BytesValue $parameter): IntegerValue|ErrorValue {
			$result = strrpos($target->literalValue, $parameter->literalValue);
			return $result === false ?
				$this->valueRegistry->error(
					$this->valueRegistry->core->sliceNotInBytes
				) : $this->valueRegistry->integer($result);
		};
	}

}

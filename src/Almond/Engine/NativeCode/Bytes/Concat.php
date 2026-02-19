<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, Type, BytesValue, Value> */
final readonly class Concat extends NativeMethod {

	protected function isParameterTypeValid(Type $parameterType, callable $validator, Type $targetType): bool {
		return $parameterType->isSubtypeOf($this->typeRegistry->bytes());
	}

	protected function getValidator(): callable {
		return function(BytesType $targetType, Type $parameterType): BytesType {
			if ($parameterType instanceof BytesType) {
				return $this->typeRegistry->bytes(
					$targetType->range->minLength + $parameterType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ||
					$parameterType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
					$targetType->range->maxLength + $parameterType->range->maxLength
				);
			}
			return $this->typeRegistry->bytes();
		};
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, Value $parameter): BytesValue {
			if ($parameter instanceof BytesValue) {
				return $this->valueRegistry->bytes($target->literalValue . $parameter->literalValue);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		};
	}
}

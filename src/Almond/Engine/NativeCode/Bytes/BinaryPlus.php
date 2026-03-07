<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, Type, BytesValue, Value> */
final readonly class BinaryPlus extends NativeMethod {

	protected function validateParameterType(Type $parameterType, Type $targetType): null|string {
		return $parameterType->isSubtypeOf(
			$this->typeRegistry->union([
				$this->typeRegistry->bytes(),
				$this->typeRegistry->integer(0, 255)
			])
		) ? null : sprintf("Parameter type %s is not a subtype of Bytes or Integer<0..255>", $parameterType);
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
			if ($parameterType->isSubtypeOf($this->typeRegistry->integer(0, 255))) {
				return $this->typeRegistry->bytes(
					$targetType->range->minLength + 1,
					$targetType->range->maxLength === PlusInfinity::value ? PlusInfinity::value :
						$targetType->range->maxLength + 1
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
			if ($parameter instanceof IntegerValue && $parameter->literalValue >= 0 && $parameter->literalValue <= 255) {
				return $this->valueRegistry->bytes($target->literalValue .
					/** @phpstan-ignore argument.type */
					chr((int)(string)$parameter->literalValue)
				);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		};
	}
}

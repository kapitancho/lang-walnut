<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Bytes;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\BytesType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BytesValue;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<BytesType, BytesType, BytesValue, BytesValue> */
final readonly class BinaryBitwiseAnd extends NativeMethod {

	protected function getValidator(): callable {
		return fn(BytesType $targetType, BytesType $parameterType): BytesType =>
			$this->typeRegistry->bytes(
				max($targetType->range->minLength, $parameterType->range->minLength),
				max($targetType->range->maxLength, $parameterType->range->maxLength)
			);
	}

	protected function getExecutor(): callable {
		return function(BytesValue $target, BytesValue $parameter): BytesValue {
			$targetBytes = $target->literalValue;
			$paramBytes = $parameter->literalValue;
			$targetLen = strlen($targetBytes);
			$paramLen = strlen($paramBytes);

			$maxLen = max($targetLen, $paramLen);
			if ($targetLen < $maxLen) {
				$targetBytes = str_pad($targetBytes, $maxLen, "\x00", STR_PAD_LEFT);
			}
			if ($paramLen < $maxLen) {
				$paramBytes = str_pad($paramBytes, $maxLen, "\x00", STR_PAD_LEFT);
			}

			$result = '';
			for ($i = 0; $i < $maxLen; $i++) {
				$result .= chr(ord($targetBytes[$i]) & ord($paramBytes[$i]));
			}

			return $this->valueRegistry->bytes($result);
		};
	}

}

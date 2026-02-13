<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use JsonException;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, NullType, StringValue, NullValue> */
final readonly class JsonDecode extends NativeMethod {

	protected function getValidator(): callable {
		return fn(StringType $targetType, NullType $parameterType): ResultType =>
			$this->typeRegistry->result(
				$this->typeRegistry->core->jsonValue,
				$this->typeRegistry->core->invalidJsonString
			);
	}

	private function phpToValue(string|int|float|bool|null|array|object $value): Value {
		return match(true) {
			is_array($value) => $this->valueRegistry->tuple(
				array_map(fn(string|int|float|bool|null|array|object $item): Value
					=> $this->phpToValue($item), $value)
			),
			is_object($value) => $this->valueRegistry->record(
				array_map(fn(string|int|float|bool|null|array|object $item): Value
					=> $this->phpToValue($item), (array)$value)
			),
			is_string($value) => $this->valueRegistry->string($value),
			is_int($value) => $this->valueRegistry->integer($value),
			is_float($value) => $this->valueRegistry->real($value),
			is_bool($value) => $this->valueRegistry->boolean($value),
			is_null($value) => $this->valueRegistry->null,
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, NullValue $parameter): Value {
			try {
				/** @var array|bool|float|int|object|string|null $value */
				$value = json_decode($target->literalValue, false, 512, JSON_THROW_ON_ERROR);
				return $this->phpToValue($value);
			} catch (JsonException) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->invalidJsonString(
						$this->valueRegistry->record(['value' => $target])
					)
				);
			}
		};
	}

}

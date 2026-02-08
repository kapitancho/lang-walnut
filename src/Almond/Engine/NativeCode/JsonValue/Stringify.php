<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\JsonValue;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<Type, NullType, Value, NullValue */
final readonly class Stringify extends NativeMethod {

	protected function getValidator(): callable {
		return fn(Type $targetType, NullType $parameterType): StringType => $this->typeRegistry->string(2);
	}

	protected function getExecutor(): callable {
		return fn(Value $target, NullValue $parameter): StringValue => $this->valueRegistry->string(
			(string)json_encode($this->doStringify($target), JSON_PRETTY_PRINT)
		);
	}

	/** @throws ExecutionException */
	private function doStringify(Value $value): string|int|float|bool|null|array|object {
		if ($value instanceof TupleValue || $value instanceof RecordValue) {
			return array_map(function ($item) {
				return $this->doStringify($item);
			}, $value->values);
		}
		if ($value instanceof SetValue) {
			$items = [];
			foreach($value->values as $item) {
				$items[] = $this->doStringify($item);
			}
			return $items;
		}
		if ($value instanceof NullValue ||
			$value instanceof BooleanValue ||
			$value instanceof StringValue
		) {
			return $value->literalValue;
		}
		if ($value instanceof IntegerValue) {
			return (int)(string)$value->literalValue;
		}
		if ($value instanceof RealValue) {
			return (float)(string)$value->literalValue;
		}
		if ($value instanceof MutableValue) {
			return $this->doStringify($value->value);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException(
			sprintf("Cannot stringify value of type %s", $value)
		);
		// @codeCoverageIgnoreEnd
	}

}
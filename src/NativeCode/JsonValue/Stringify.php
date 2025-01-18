<?php

namespace Walnut\Lang\NativeCode\JsonValue;

use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\SubtypeValue;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class Stringify implements NativeMethod {

	public function __construct(
	) {}

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): StringType {
		return $programRegistry->typeRegistry->string();
	}

	private function doStringify(Value $value): string|int|float|bool|null|array|object {
		if ($value instanceof TupleValue) {
			$items = [];
			foreach($value->values as $item) {
				$items[] = $this->doStringify($item);
			}
			return $items;
		}
		if ($value instanceof RecordValue) {
			$items = [];
			foreach($value->values as $key => $item) {
				$items[$key] = $this->doStringify($item);
			}
			return $items;
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
		if ($value instanceof SubtypeValue) {
			return $this->doStringify($value->baseValue);
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

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		return TypedValue::forValue($programRegistry->valueRegistry->string(
			json_encode($this->doStringify($targetValue), JSON_PRETTY_PRINT)
		));
	}

}
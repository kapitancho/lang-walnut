<?php

namespace Walnut\Lang\NativeCode\String;

use JsonException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\StringValue;

final readonly class JsonDecode implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			return $programRegistry->typeRegistry->result(
				$programRegistry->typeRegistry->withName(new TypeNameIdentifier("JsonValue")),
				$programRegistry->typeRegistry->withName(new TypeNameIdentifier("InvalidJsonString"))
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function phpToValue(ProgramRegistry $programRegistry, string|int|float|bool|null|array|object $value): Value {
		return match(true) {
			is_array($value) => $programRegistry->valueRegistry->tuple(
				array_map(fn(string|int|float|bool|null|array|object $item): Value
					=> $this->phpToValue($programRegistry, $item), $value)
			),
			is_object($value) => $programRegistry->valueRegistry->record(
				array_map(fn(string|int|float|bool|null|array|object $item): Value
					=> $this->phpToValue($programRegistry, $item), (array)$value)
			),
			is_string($value) => $programRegistry->valueRegistry->string($value),
			is_int($value) => $programRegistry->valueRegistry->integer($value),
			is_float($value) => $programRegistry->valueRegistry->real($value),
			is_bool($value) => $programRegistry->valueRegistry->boolean($value),
			is_null($value) => $programRegistry->valueRegistry->null,
		};
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof StringValue) {
			try {
				$value = json_decode($targetValue->literalValue, false, 512, JSON_THROW_ON_ERROR);
				return new TypedValue(
					$programRegistry->typeRegistry->withName(new TypeNameIdentifier("JsonValue")),
					$this->phpToValue($programRegistry, $value)
				);
			} catch (JsonException) {
				return TypedValue::forValue($programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->sealedValue(
						new TypeNameIdentifier("InvalidJsonString"),
						$programRegistry->valueRegistry->record(['value' => $targetValue])
					)
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
<?php

namespace Walnut\Lang\NativeCode\String;

use JsonException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\StringValue;

final readonly class JsonDecode implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			return $this->context->typeRegistry->result(
				$this->context->typeRegistry->withName(new TypeNameIdentifier("JsonValue")),
				$this->context->typeRegistry->withName(new TypeNameIdentifier("InvalidJsonString"))
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	private function phpToValue(string|int|float|bool|null|array|object $value): Value {
		return match(true) {
			is_array($value) => $this->context->valueRegistry->tuple(
				array_map(fn(string|int|float|bool|null|array|object $item): Value
					=> $this->phpToValue($item), $value)
			),
			is_object($value) => $this->context->valueRegistry->record(
				array_map(fn(string|int|float|bool|null|array|object $item): Value
					=> $this->phpToValue($item), (array)$value)
			),
			is_string($value) => $this->context->valueRegistry->string($value),
			is_int($value) => $this->context->valueRegistry->integer($value),
			is_float($value) => $this->context->valueRegistry->real($value),
			is_bool($value) => $this->context->valueRegistry->boolean($value),
			is_null($value) => $this->context->valueRegistry->null,
		};
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof StringValue) {
			try {
				$value = json_decode($targetValue->literalValue, false, 512, JSON_THROW_ON_ERROR);
				return new TypedValue(
					$this->context->typeRegistry->withName(new TypeNameIdentifier("JsonValue")),
					$this->phpToValue($value)
				);
			} catch (JsonException) {
				return TypedValue::forValue($this->context->valueRegistry->error(
					$this->context->valueRegistry->sealedValue(
						new TypeNameIdentifier("InvalidJsonString"),
						$this->context->valueRegistry->record(['value' => $targetValue])
					)
				));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}
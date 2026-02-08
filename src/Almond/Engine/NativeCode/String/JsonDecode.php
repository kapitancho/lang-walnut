<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use JsonException;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class JsonDecode implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType) {
			return $this->validationFactory->validationSuccess(
				$this->typeRegistry->result(
					$this->typeRegistry->core->jsonValue,
					$this->typeRegistry->core->invalidJsonString
				)
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			$origin
		);
		// @codeCoverageIgnoreEnd
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

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof StringValue) {
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
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}

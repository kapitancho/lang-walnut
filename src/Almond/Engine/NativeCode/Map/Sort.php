<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\BooleanValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RealValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Sort implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof RecordType) {
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($this->typeRegistry->string()) || $itemType->isSubtypeOf(
				$this->typeRegistry->union([
					$this->typeRegistry->integer(),
					$this->typeRegistry->real()
				])
			)) {
				$pType = $this->typeRegistry->union([
					$this->typeRegistry->null,
					$this->typeRegistry->record([
						'reverse' => $this->typeRegistry->boolean
					], null)
				]);
				if ($parameterType->isSubtypeOf($pType)) {
					return $this->validationFactory->validationSuccess($targetType);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("The parameter type %s is not a subtype of %s", $parameterType, $pType),
					origin: $origin
				);
			}
			// @codeCoverageIgnoreStart
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
				origin: $origin
			);
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RecordValue) {
			if ($parameter instanceof NullValue || (
				$parameter instanceof RecordValue &&
				($rev = $parameter->values['reverse'] ?? null) instanceof BooleanValue
			)) {
				$reverse = isset($rev) ? $rev->literalValue : false;
				$sort = $reverse ? arsort(...) : asort(...);

				$values = $target->values;

				$rawValues = [];
				$hasStrings = false;
				$hasNumbers = false;
				foreach($values as $key => $value) {
					if ($value instanceof StringValue) {
						$hasStrings = true;
					} elseif ($value instanceof IntegerValue || $value instanceof RealValue) {
						$hasNumbers = true;
					} else {
						// @codeCoverageIgnoreStart
						throw new ExecutionException("Invalid target value");
						// @codeCoverageIgnoreEnd
					}
					$rawValues[$key] = (string)$value->literalValue;
				}
				if ($hasStrings) {
					// @codeCoverageIgnoreStart
					if ($hasNumbers) {
						throw new ExecutionException("Invalid target value");
					}
					// @codeCoverageIgnoreEnd
					$sort($rawValues, SORT_STRING);
					return $this->valueRegistry->record(array_map(
						fn(string $value) => $this->valueRegistry->string($value),
						$rawValues
					));
				}
				$sort($rawValues, SORT_NUMERIC);
				return $this->valueRegistry->record(array_map(
					fn(string $value) => str_contains((string)$value, '.') ?
						$this->valueRegistry->real((float)$value) :
						$this->valueRegistry->integer((int)$value),
					$rawValues
				));
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}

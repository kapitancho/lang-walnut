<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\IntegerValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberIntervalEndpoint;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class CountValues implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$itemType = $targetType->itemType;
			if ($itemType->isSubtypeOf($this->typeRegistry->string()) ||
				$itemType->isSubtypeOf($this->typeRegistry->integer())
			) {
				if ($itemType->isSubtypeOf($this->typeRegistry->string())) {
					$keyType = $itemType;
				} else {
					$baseItemType = $this->toBaseType($itemType);
					$keyType = $this->typeRegistry->string(
						1,
						($max = $baseItemType->numberRange->max) instanceof NumberIntervalEndpoint &&
						($min = $baseItemType->numberRange->min) instanceof NumberIntervalEndpoint ?
							max(1,
								(int)ceil(log10(abs((int)(string)$max->value))),
								(int)ceil(log10(abs((int)(string)$min->value))) +
								($min->value < 0 ? 1 : 0)
							) : 1000
					);
				}
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->map(
						$this->typeRegistry->integer(1, max(1, $targetType->range->maxLength)),
						min(1, $targetType->range->minLength),
						$targetType->range->maxLength,
						$keyType
					)
				);
			}
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
		if ($target instanceof TupleValue) {
			$rawValues = [];
			foreach ($target->values as $value) {
				if ($value instanceof StringValue) {
					$rawValues[] = $value->literalValue;
				} elseif ($value instanceof IntegerValue) {
					$rawValues[] = (string)$value->literalValue;
				} else {
					// @codeCoverageIgnoreStart
					throw new ExecutionException("Invalid target value");
					// @codeCoverageIgnoreEnd
				}
			}
			$rawValues = array_count_values($rawValues);
			return $this->valueRegistry->record(array_map(
				fn($value) => $this->valueRegistry->integer($value),
				$rawValues
			));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}

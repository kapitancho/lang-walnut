<?php /** @noinspection PhpUnusedParameterInspection */

namespace Walnut\Lang\Implementation\Code\NativeCode\Hydrator;

use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\FalseType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\TrueType;
use Walnut\Lang\Blueprint\Value\BooleanValue;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\IntegerValue;
use Walnut\Lang\Blueprint\Value\NullValue;
use Walnut\Lang\Blueprint\Value\RealValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\HydrationException;

final readonly class SimpleTypeHydrator {
	public function __construct(
		private ValueRegistry $valueRegistry,
	) {}

	public function hydrateAny(Value $value, AnyType $targetType, string $hydrationPath): Value {
		return $value;
	}

	public function hydrateNothing(Value $value, NothingType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("There is no value allowed, %s provided", $value)
		);
	}

	public function hydrateFunction(Value $value, FunctionType $targetType, string $hydrationPath): Value {
		throw new HydrationException(
			$value,
			$hydrationPath,
			"Functions cannot be hydrated"
		);
	}

	public function hydrateInteger(Value $value, IntegerType $targetType, string $hydrationPath): IntegerValue {
		if ($value instanceof IntegerValue) {
			if ($targetType->contains($value->literalValue)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The integer value should be in %s",
					$targetType->numberRange,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be an integer in %s",
				$targetType->numberRange,
			)
		);
	}

	public function hydrateBoolean(Value $value, BooleanType $targetType, string $hydrationPath): BooleanValue {
		if ($value instanceof BooleanValue) {
			return $value;
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be a boolean"
		);
	}

	public function hydrateNull(Value $value, NullType $targetType, string $hydrationPath): NullValue {
		if ($value instanceof NullValue) {
			return $value;
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be 'null'"
		);
	}

	public function hydrateTrue(Value $value, TrueType $targetType, string $hydrationPath): BooleanValue {
		if ($value instanceof BooleanValue) {
			if ($value->literalValue === true) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				"The boolean value should be true"
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be 'true'"
		);
	}

	public function hydrateFalse(Value $value, FalseType $targetType, string $hydrationPath): BooleanValue {
		if ($value instanceof BooleanValue) {
			if ($value->literalValue === false) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				"The boolean value should be false"
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			"The value should be 'false'"
		);
	}

	public function hydrateString(Value $value, StringType $targetType, string $hydrationPath): StringValue {
		if ($value instanceof StringValue) {
			$l = mb_strlen($value->literalValue);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The string value should be with a length between %s and %s",
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string with a length between %s and %s",
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
			)
		);
	}

	public function hydrateBytes(Value $value, BytesType $targetType, string $hydrationPath): BytesValue {
		if ($value instanceof StringValue) {
			$l = strlen($value->literalValue);
			if ($targetType->range->minLength <= $l && (
					$targetType->range->maxLength === PlusInfinity::value ||
					$targetType->range->maxLength >= $l
			)) {
				return $this->valueRegistry->bytes($value->literalValue);
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The string value should be with a raw length between %s and %s",
					$targetType->range->minLength,
					$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string with a raw length between %s and %s",
				$targetType->range->minLength,
				$targetType->range->maxLength === PlusInfinity::value ? "+Infinity" : $targetType->range->maxLength,
			)
		);
	}

	public function hydrateStringSubset(Value $value, StringSubsetType $targetType, string $hydrationPath): StringValue {
		if ($value instanceof StringValue) {
			if ($targetType->contains($value->literalValue)) {
				return $value;
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The string value should be among %s",
					implode(', ', $targetType->subsetValues)
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a string among %s",
				implode(', ', $targetType->subsetValues)
			)
		);
	}

	public function hydrateReal(Value $value, RealType $targetType, string $hydrationPath): RealValue {
		if ($value instanceof IntegerValue || $value instanceof RealValue) {
			if ($targetType->contains($value->literalValue)) {
				return $this->valueRegistry->real((float)(string)$value->literalValue);
			}
			throw new HydrationException(
				$value,
				$hydrationPath,
				sprintf("The real value should be in %s",
					$targetType->numberRange
				)
			);
		}
		throw new HydrationException(
			$value,
			$hydrationPath,
			sprintf("The value should be a real number in %s",
				$targetType->numberRange
			)
		);
	}

}
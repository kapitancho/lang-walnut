<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\IntegerValue as IntegerValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\IntegerSubsetType;

final class IntegerValue implements IntegerValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly int $literalValue
    ) {}

	public IntegerSubsetType $type {
		get => $this->typeRegistry->integerSubset([$this]);
    }

	public function asRealValue(): RealValue {
		return new RealValue($this->typeRegistry, (float)$this->literalValue);
	}

	public function equals(Value $other): bool {
		return ($other instanceof IntegerValueInterface && $this->literalValue === $other->literalValue) ||
			($other instanceof RealValue && $this->asRealValue()->literalValue === $other->literalValue);
	}

	public function __toString(): string {
		return (string)$this->literalValue;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Integer',
			'value' => $this->literalValue
		];
	}
}
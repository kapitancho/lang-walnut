<?php

namespace Walnut\Lang\Implementation\Value;

use BcMath\Number;
use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\IntegerValue as IntegerValueInterface;
use Walnut\Lang\Blueprint\Value\RealValue as RealValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\RealSubsetType;

final class RealValue implements RealValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly Number $literalValue
    ) {}

	public RealSubsetType $type {
		get => $this->typeRegistry->realSubset([$this]);
    }

	public function equals(Value $other): bool {
		return ($other instanceof RealValueInterface && $this->literalValue === $other->literalValue) ||
			($other instanceof IntegerValueInterface && $this->literalValue === $other->asRealValue()->literalValue);
	}

	public function __toString(): string {
		return (string) $this->literalValue;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Real',
			'value' => $this->literalValue
		];
	}

}
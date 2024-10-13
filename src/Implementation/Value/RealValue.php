<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\IntegerValue as IntegerValueInterface;
use Walnut\Lang\Blueprint\Value\RealValue as RealValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\RealSubsetType;

final readonly class RealValue implements RealValueInterface, JsonSerializable {

    public function __construct(
		private TypeRegistry $typeRegistry,
		private float $realValue
    ) {}

    public function type(): RealSubsetType {
        return $this->typeRegistry->realSubset([$this]);
    }

    public function literalValue(): float {
        return $this->realValue;
    }

	public function equals(Value $other): bool {
		return ($other instanceof RealValueInterface && $this->literalValue() === $other->literalValue()) ||
			($other instanceof IntegerValueInterface && $this->literalValue() === $other->asRealValue()->literalValue());
	}

	public function __toString(): string {
		return (string) $this->realValue;
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Real',
			'value' => $this->realValue
		];
	}

}
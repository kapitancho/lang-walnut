<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue as MutableValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\MutableType;

final class MutableValue implements MutableValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		private readonly Type $targetType,
	    private Value $value
    ) {}

    public function type(): MutableType {
        return $this->typeRegistry->mutable($this->targetType);
    }

	public function targetType(): Type {
		return $this->targetType;
	}

    public function value(): Value {
		return $this->value;
    }

	public function changeValueTo(Value $value): void {
		$this->value = $value;
	}

	public function equals(Value $other): bool {
		return $other instanceof MutableValueInterface &&
			$this->targetType->isSubtypeOf($other->targetType()) &&
			$other->targetType()->isSubtypeOf($this->targetType) &&
			$this->value->equals($other->value());
	}

	public function __toString(): string {
		return sprintf(
			"Mutable[%s, %s]",
			$this->targetType,
			$this->value
		);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Mutable',
			'targetType' => $this->targetType,
			'value' => $this->value
		];
	}
}
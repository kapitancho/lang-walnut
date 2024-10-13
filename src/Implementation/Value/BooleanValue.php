<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Value\BooleanValue as BooleanValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class BooleanValue implements BooleanValueInterface, JsonSerializable {

    public function __construct(
        private TypeRegistry $typeRegistry,
        private EnumValueIdentifier $valueIdentifier,
	    private bool $value
    ) {}

    public function type(): EnumerationSubsetType {
        return $this->enumeration()->subsetType([$this->valueIdentifier]);
    }

    public function enumeration(): BooleanType {
        return $this->typeRegistry->boolean();
    }

    public function name(): EnumValueIdentifier {
        return $this->valueIdentifier;
    }

	public function literalValue(): bool {
		return $this->value;
	}

	public function equals(Value $other): bool {
		return $other instanceof BooleanValueInterface && $this->literalValue() === $other->literalValue();
	}

	public function __toString(): string {
		return $this->value ? 'true' : 'false';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Boolean',
			'value' => $this->value ? 'true' : 'false'
		];
	}
}
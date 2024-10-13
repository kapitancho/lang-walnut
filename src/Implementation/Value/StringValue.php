<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\StringValue as StringValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\StringSubsetType;

final readonly class StringValue implements StringValueInterface, JsonSerializable {

    public function __construct(
		private TypeRegistry $typeRegistry,
		private string $stringValue
    ) {}

    public function type(): StringSubsetType {
        return $this->typeRegistry->stringSubset([$this]);
    }

    public function literalValue(): string {
        return $this->stringValue;
    }

	public function equals(Value $other): bool {
		return $other instanceof StringValueInterface && $this->literalValue() === $other->literalValue();
	}

	public function __toString(): string {
		return "'" . str_replace(['\\', "\n", "'"], ['\\\\', '\n', '\`'], $this->literalValue()) . "'";
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'String',
			'value' => $this->stringValue
		];
	}
}
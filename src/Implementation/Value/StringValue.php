<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Value\StringValue as StringValueInterface;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\StringSubsetType;

final class StringValue implements StringValueInterface, JsonSerializable {

    public function __construct(
		private readonly TypeRegistry $typeRegistry,
		public readonly string $literalValue
    ) {}

	public StringSubsetType $type {
		get => $this->typeRegistry->stringSubset([$this->literalValue]);
    }

	public function equals(Value $other): bool {
		return $other instanceof StringValueInterface && $this->literalValue === $other->literalValue;
	}

	public function __toString(): string {
		return "'" . str_replace(['\\', "\n", "'"], ['\\\\', '\n', '\`'], $this->literalValue) . "'";
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'String',
			'value' => $this->literalValue
		];
	}
}
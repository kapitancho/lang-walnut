<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Value\NullValue as NullValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class NullValue implements NullValueInterface, JsonSerializable {

    public function __construct(
        private TypeRegistry $typeRegistry
    ) {}

    public function type(): NullType {
        return $this->typeRegistry->null();
    }

	public function literalValue(): null {
		return null;
	}

	public function equals(Value $other): bool {
		return $other instanceof NullValueInterface;
	}

	public function __toString(): string {
		return 'null';
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Null'
		];
	}

}
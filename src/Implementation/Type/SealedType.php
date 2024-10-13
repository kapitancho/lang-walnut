<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SealedType as SealedTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class SealedType implements SealedTypeInterface, JsonSerializable {

    public function __construct(
	    private TypeNameIdentifier $typeName,
        private RecordType $valueType
    ) {}

	public function name(): TypeNameIdentifier {
		return $this->typeName;
    }

	public function valueType(): RecordType {
        return $this->valueType;
    }

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof SealedTypeInterface => $this->name()->equals($ofType->name()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return (string)$this->typeName;
	}

	public function jsonSerialize(): array {
		return ['type' => 'Sealed', 'name' => $this->typeName, 'valueType' => $this->valueType];
	}
}
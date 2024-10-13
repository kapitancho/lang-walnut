<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;

final readonly class AtomType implements AtomTypeInterface, JsonSerializable {

    public function __construct(
        private TypeNameIdentifier $typeName,
        private AtomValue          $atomValue
    ) {}

    public function value(): AtomValue {
        return $this->atomValue;
    }

    public function name(): TypeNameIdentifier {
        return $this->typeName;
    }

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof AtomTypeInterface => $this->typeName->equals($ofType->name()),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return (string)$this->typeName;
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Atom',
			'name' => $this->typeName
		];
	}
}
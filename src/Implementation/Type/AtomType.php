<?php

namespace Walnut\Lang\Implementation\Type;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Type\AtomType as AtomTypeInterface;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;

final readonly class AtomType implements AtomTypeInterface, JsonSerializable {

    public function __construct(
        public TypeNameIdentifier $name,
        public AtomValue           $value
    ) {}

    public function isSubtypeOf(Type $ofType): bool {
		return match(true) {
			$ofType instanceof AtomTypeInterface => $this->name->equals($ofType->name),
			$ofType instanceof SupertypeChecker => $ofType->isSupertypeOf($this),
			default => false
		};
    }

	public function __toString(): string {
		return (string)$this->name;
	}

	public function jsonSerialize(): array {
		return [
			'type' => 'Atom',
			'name' => $this->name
		];
	}
}
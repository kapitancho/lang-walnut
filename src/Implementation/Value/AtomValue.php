<?php

namespace Walnut\Lang\Implementation\Value;

use JsonSerializable;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Value\AtomValue as AtomValueInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class AtomValue implements AtomValueInterface, JsonSerializable {

    public function __construct(
        private readonly TypeRegistry $typeRegistry,
        private readonly TypeNameIdentifier $typeName
    ) {}

	public AtomType $type {
        get => $this->typeRegistry->atom($this->typeName);
    }

	public function equals(Value $other): bool {
		return $other instanceof AtomValueInterface && $this->typeName->equals($other->type->name);
	}

	public function __toString(): string {
		return sprintf("%s[]", $this->typeName);
	}

	public function jsonSerialize(): array {
		return [
			'valueType' => 'Atom',
			'typeName' => $this->typeName
		];
	}
}
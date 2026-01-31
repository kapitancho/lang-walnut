<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier;

use JsonSerializable;

final readonly class TypeName implements JsonSerializable {
	/** @throws IdentifierException */
	public function __construct(
		public string $identifier
	) {
		preg_match('/^[A-Z][a-zA-Z0-9_]*$/', $identifier) ||
		IdentifierException::invalidTypeNameIdentifier($identifier);
	}

	public function equals(TypeName $other): bool {
		return $this->identifier === $other->identifier;
	}

	public function __toString(): string {
		return $this->identifier;
	}

	public function jsonSerialize(): string {
		return $this->identifier;
	}

	public function asVariableName(): VariableName {
		return new VariableName(lcfirst($this->identifier));
	}

	public function asMethodName(): MethodName {
		return new MethodName($this->identifier);
	}
}
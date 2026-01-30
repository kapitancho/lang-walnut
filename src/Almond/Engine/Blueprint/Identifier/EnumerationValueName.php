<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Identifier;

use JsonSerializable;

final readonly class EnumerationValueName implements JsonSerializable {
	/** @throws IdentifierException */
	public function __construct(
		public string $identifier
	) {
		preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $identifier) ||
			IdentifierException::invalidEnumValueIdentifier($identifier);
	}

	public function equals(EnumerationValueName $other): bool {
		return $this->identifier === $other->identifier;
	}

	public function __toString(): string {
		return $this->identifier;
	}

	public function jsonSerialize(): string {
		return $this->identifier;
	}
}
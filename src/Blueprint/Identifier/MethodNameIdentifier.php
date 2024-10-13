<?php

namespace Walnut\Lang\Blueprint\Identifier;

use JsonSerializable;

final readonly class MethodNameIdentifier implements JsonSerializable {
	/** @throws IdentifierException */
	public function __construct(
		public string $identifier
	) {
		preg_match('/^(\w+)$/', $identifier) ||
			IdentifierException::invalidMethodNameIdentifier($identifier);
	}

	public function equals(MethodNameIdentifier $identifier): bool {
		return $this->identifier === $identifier->identifier;
	}

	public function __toString(): string {
		return $this->identifier;
	}

	public function jsonSerialize(): string {
		return $this->identifier;
	}
}
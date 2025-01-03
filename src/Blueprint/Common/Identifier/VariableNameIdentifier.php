<?php

namespace Walnut\Lang\Blueprint\Common\Identifier;

use JsonSerializable;

final readonly class VariableNameIdentifier implements JsonSerializable {
	/** @throws IdentifierException */
	public function __construct(
		public string $identifier
	) {
		preg_match('/^(([a-z][a-zA-Z0-9]*)|#|%(\w+)|%|#(\w+)|\$|\$(\w+)|_)$/', $identifier) ||
			IdentifierException::invalidVariableNameIdentifier($identifier);
	}

	public function equals(VariableNameIdentifier $other): bool {
		return $this->identifier === $other->identifier;
	}

	public function __toString(): string {
		return $this->identifier;
	}

	public function jsonSerialize(): string {
		return $this->identifier;
	}
}
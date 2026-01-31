<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier;

use JsonSerializable;

final readonly class VariableName implements JsonSerializable {
	/** @throws IdentifierException */
	public function __construct(
		public string $identifier
	) {
		preg_match('/^(([a-z][a-zA-Z0-9_]*)|#|%(\d+)|%(\w+)|%|#(\w+)|#(\d+)|\$(\d+)|\$|\$\$|\$(\w+)|_)$/', $identifier) ||
			IdentifierException::invalidVariableNameIdentifier($identifier);
	}

	public function equals(VariableName $other): bool {
		return $this->identifier === $other->identifier;
	}

	public function __toString(): string {
		return $this->identifier;
	}

	public function jsonSerialize(): string {
		return $this->identifier;
	}
}
<?php

namespace Walnut\Lang\Blueprint\Common\Identifier;

use JsonSerializable;

final readonly class MethodNameIdentifier implements JsonSerializable {
	/** @throws IdentifierException */
	public function __construct(
		public string $identifier
	) {
		self::isValidIdentifier($this->identifier) ||
			IdentifierException::invalidMethodNameIdentifier($identifier);
	}

	public static function isValidIdentifier(string $identifier): bool {
		return preg_match('/^(\w+)$/', $identifier) === 1;
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
<?php

namespace Walnut\Lang\Blueprint\Identifier;

use LogicException;

final class IdentifierException extends LogicException {

	private const InvalidVariableNameIdentifier = "Invalid variable name identifier: %s";
	private const InvalidPropertyNameIdentifier = "Invalid property name identifier: %s";
	private const InvalidMethodNameIdentifier = "Invalid method name identifier: %s";
	private const InvalidTypeNameIdentifier = "Invalid type name identifier: %s";
	private const InvalidEnumValueIdentifier = "Invalid enum value identifier: %s";

	public static function invalidVariableNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidVariableNameIdentifier, $identifier));
	}

	public static function invalidTypeNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidTypeNameIdentifier, $identifier));
	}

	public static function invalidEnumValueIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidEnumValueIdentifier, $identifier));
	}

	public static function invalidPropertyNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidPropertyNameIdentifier, $identifier));
	}

	public static function invalidMethodNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidMethodNameIdentifier, $identifier));
	}
}
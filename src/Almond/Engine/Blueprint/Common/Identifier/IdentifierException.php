<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier;

use LogicException;

final class IdentifierException extends LogicException {

	private const string InvalidVariableNameIdentifier = "Invalid variable name identifier: %s";
	private const string InvalidMethodNameIdentifier = "Invalid method name identifier: %s";
	private const string InvalidTypeNameIdentifier = "Invalid type name identifier: %s";
	private const string InvalidEnumValueIdentifier = "Invalid enum value identifier: %s";

	public static function invalidVariableNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidVariableNameIdentifier, $identifier));
	}

	public static function invalidTypeNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidTypeNameIdentifier, $identifier));
	}

	public static function invalidEnumValueIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidEnumValueIdentifier, $identifier));
	}

	public static function invalidMethodNameIdentifier(string $identifier): never {
		throw new self(sprintf(self::InvalidMethodNameIdentifier, $identifier));
	}
}
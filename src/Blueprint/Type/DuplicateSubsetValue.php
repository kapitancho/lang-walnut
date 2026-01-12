<?php

namespace Walnut\Lang\Blueprint\Type;

use BcMath\Number;
use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;

final class DuplicateSubsetValue extends InvalidArgumentException {
	private const string enumerationType = 'enumeration';
	private const string integerType = 'integer';
	private const string realType = 'real';
	private const string stringType = 'string';

    public function __construct(
        public readonly string $subsetType,
        public readonly string $typeName,
        public readonly string $valueName,
    ) {
        parent::__construct(
            sprintf(
                "Duplicate %s value '%s' in type '%s'",
	            $subsetType, $valueName, $typeName)
        );
    }

    private static function of(
	    string $subsetType,
        string $typeName,
        string $valueName
    ): never {
        throw new self($subsetType, $typeName, $valueName);
    }

	public static function ofEnumeration(
		string $typeName,
		EnumValueIdentifier $enumValue
	): never {
		self::of(self::enumerationType, $typeName, $enumValue->identifier);
	}

	public static function ofInteger(string $typeName, Number $value): never {
		self::of(self::integerType, $typeName, $value);
	}

	public static function ofReal(string $typeName, Number $value): never {
		self::of(self::realType, $typeName, $value);
	}

	public static function ofString(string $typeName, string $value): never {
		self::of(self::stringType, $typeName, $value);
	}

}
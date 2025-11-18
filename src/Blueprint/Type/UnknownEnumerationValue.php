<?php

namespace Walnut\Lang\Blueprint\Type;

use InvalidArgumentException;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;

final class UnknownEnumerationValue extends InvalidArgumentException {
    public function __construct(
        public readonly string $typeName,
        public readonly string $valueName,
    ) {
        parent::__construct(
            sprintf(
                "Unknown enumeration value: '%s' for type '%s'",
                $valueName, $typeName)
        );
    }

	/** @throws self */
    public static function of(
        TypeNameIdentifier $typeName,
        EnumValueIdentifier $enumValue
    ): never {
        throw new self($typeName->identifier, $enumValue->identifier);
    }
}